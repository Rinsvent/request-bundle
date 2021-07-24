<?php
namespace Rinsvent\RequestBundle\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Rinsvent\ApiSDKGenerator\DTO\Writer\Config;
use Rinsvent\ApiSDKGenerator\Service\Writer;

class Unit extends \Codeception\Module
{
    public function getWriter(string $lang = 'php'): Writer
    {
        return new Writer(
            new Config(
                dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'templates',
                dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'var/tests/cache',
                $lang,
                    dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'var/tests/result',
                'Rinsvent\\AuthSDK'
            )
        );
    }
}
