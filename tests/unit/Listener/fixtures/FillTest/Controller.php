<?php

namespace Rinsvent\RequestBundle\Tests\unit\Listener\fixtures\FillTest;

use Rinsvent\RequestBundle\Annotation\RequestDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    #[RequestDTO(className: HelloRequest::class)]
    public function hello(Request $request)
    {
        return new Response(
            sprintf("Hello %s", $request->get('name'))
        );
    }
}
