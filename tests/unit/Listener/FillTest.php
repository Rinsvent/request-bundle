<?php

namespace Rinsvent\RequestBundle\Tests\Listener;

use Symfony\Component\HttpFoundation\Request;

use Rinsvent\RequestBundle\EventListener\RequestListener;

class FillTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSuccessFillRequestData()
    {
        $request = Request::create('/hello/igor', 'GET', [
            'surname' => 'Surname'
        ]);
        $response = $this->tester->send($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Surname', $request->get(RequestListener::REQUEST_DATA)->surname);
        $this->assertEquals('Hello igor', $response->getContent());

    }

    public function testFailRequestData()
    {
        $request = Request::create('/hello/igor', 'GET', [
            'surname' => ''
        ]);
        $response = $this->tester->send($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"errors":[{"message":"This value should not be blank.","path":"surname"}]}', $response->getContent());
    }
}
