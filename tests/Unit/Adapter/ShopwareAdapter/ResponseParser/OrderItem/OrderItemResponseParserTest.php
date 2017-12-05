<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParser;

/**
 * Class OrderItemResponseParserTest
 *
 * @group ResponseParser
 */
class OrderItemResponseParserTest extends ResponseParserTest
{
    /**
     * @var OrderItemResponseParser
     */
    private $responseParser;

    protected function setUp()
    {
        parent::setUp();

        $this->responseParser = $parser = new OrderItemResponseParser(
            $this->identityService
        );
    }

    public function testOrderItemParsing()
    {
        /**
         * @var OrderItem|null $orderItem
         */
        $orderItem = $this->responseParser->parse(self::$orderData['details'][0]);

        if (null === $orderItem) {
            $this->fail('orderItem not generated');
        }

        self::assertInstanceOf(Attribute::class, $orderItem->getAttributes()[0]);
        self::assertSame('ESD Download Artikel', $orderItem->getName());
        self::assertSame('SW10196', $orderItem->getNumber());
        self::assertSame(836.134, $orderItem->getPrice());
        self::assertSame(1.0, $orderItem->getQuantity());
        self::assertSame(OrderItem::TYPE_PRODUCT, $orderItem->getType());
    }
}
