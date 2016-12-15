<?php

namespace PlentyConnector\Tests\Integration;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit_Framework_TestCase;

/**
 * Class ContainerTest
 */
class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function test_if_mock_handler_is_available()
    {
        /**
         * @var MockHandler $handler
         */
        $handler = Shopware()->Container()->get('plentymarkets_adapter.http_client.handler');

        $this->assertInstanceOf(MockHandler::class, $handler);
    }
}
