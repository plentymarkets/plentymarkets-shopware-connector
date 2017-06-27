<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\OrderItem;

use Doctrine\ORM\EntityManagerInterface;
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

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->responseParser = $parser = new OrderItemResponseParser(
            $this->identityService,
            $entityManager
        );
    }

    public function testOrderItemParsing()
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = $this->responseParser->parse(self::$orderData['details'][0]);

        self::assertInstanceOf(Attribute::class, $orderItem->getAttributes()[0]);
        self::assertSame('ESD Download Artikel', $orderItem->getName());
        self::assertSame('SW10196', $orderItem->getNumber());
        self::assertSame(836.134, $orderItem->getPrice());
        self::assertSame(1.0, $orderItem->getQuantity());
        self::assertSame(OrderItem::TYPE_PRODUCT, $orderItem->getType());
    }
}
