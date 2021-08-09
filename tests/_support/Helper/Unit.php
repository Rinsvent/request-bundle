<?php
namespace Rinsvent\RequestBundle\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Rinsvent\RequestBundle\EventListener\RequestListener;
use Rinsvent\RequestBundle\Tests\unit\Listener\fixtures\FillTest\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Unit extends \Codeception\Module
{
    public function send(Request $request): Response
    {
        $routes = new RouteCollection();
        $controller = new Controller();
        $routes->add('hello', new Route('/hello/{name}', [
                '_controller' => [$controller, 'hello']
            ]
        ));

        $matcher = new UrlMatcher($routes, new RequestContext());
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
        $listener = new RequestListener();
        $dispatcher->addListener('kernel.request', [$listener, 'onKernelRequest']);

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();
        $kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
        $response = $kernel->handle($request);
        $response->send();
        return $response;
    }
}
