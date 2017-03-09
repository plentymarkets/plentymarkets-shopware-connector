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

    public function setUp()
    {
        parent::setup();

        $this->responseParser = $parser = new OrderItemResponseParser(
            $this->identityService
        );
    }

    public function testOrderItemParsing()
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = $this->responseParser->parse(self::$orderData['details'][0]);

        $this->assertInstanceOf(Attribute::class, $orderItem->getAttributes()[0]);
        $this->assertSame('ESD Download Artikel', $orderItem->getName());
        $this->assertSame('20001', $orderItem->getNumber());
        $this->assertSame(836.134, $orderItem->getPrice());
        $this->assertSame(1.0, $orderItem->getQuantity());
        $this->assertSame(OrderItem::TYPE_PRODUCT, $orderItem->getType());
    }
}
