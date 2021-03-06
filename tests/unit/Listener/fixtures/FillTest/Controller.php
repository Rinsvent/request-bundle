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

    #[RequestDTO(className: HelloRequest::class, jsonPath: '$.user')]
    #[RequestDTO(className: BuyRequest::class, jsonPath: '$.guest')]
    public function hello2(Request $request)
    {
        return new Response(
            sprintf("Hello %s", $request->get('name'))
        );
    }
}
