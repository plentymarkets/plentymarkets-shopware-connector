<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParser;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParser;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParser;

/**
 * Class OrderResponseParserTest
 *
 * @group ResponseParser
 */
class OrderResponseParserTest extends ResponseParserTest
{
    /**
     * @var OrderResponseParser
     */
    private $orderResponseParser;

    public function setUp()
    {
        parent::setup();

        $orderItemParser = new OrderItemResponseParser($this->identityService);
        $addressParser = new AddressResponseParser($this->identityService);

        $customerGroup = $this->createMock(Group::class);
        $customerGroup->expects($this->any())->method('getId')->willReturn(1);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())->method('findOneBy')->with(['key' => 'H'])->willReturn($customerGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        $customerParser = new CustomerResponseParser($this->identityService, $entityManager);

        /** @var OrderResponseParser $parser */
        $this->orderResponseParser = $parser = new OrderResponseParser(
            $this->identityService,
            $orderItemParser,
            $addressParser,
            $customerParser
        );
    }

    public function testOrderParsing()
    {
        /**
         * @var Order $orderDto
         */
        $orderDto = $this->orderResponseParser->parse(self::$orderData);

        $this->assertInstanceOf(Attribute::class, $orderDto->getAttributes()[0]);
        $this->assertInstanceOf(Address::class, $orderDto->getBillingAddress());
        $this->assertSame([], $orderDto->getComments());
        $this->assertInstanceOf(Customer::class, $orderDto->getCustomer());
        $this->assertInstanceOf(OrderItem::class, $orderDto->getOrderItems()[0]);
        $this->assertSame('20001', $orderDto->getOrderNumber());
        $this->assertSame(
            \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                '2012-08-30 10:15:54',
                new \DateTimeZone('Europe/Berlin')
            )->format(DATE_W3C),
            $orderDto->getOrderTime()->format(DATE_W3C)
        );
        $this->assertSame(Order::TYPE_ORDER, $orderDto->getOrderType());
       // $this->assertInstanceOf(PaymentMethod::class, $orderDto->getPayments()[0]);
        $this->assertInstanceOf(Address::class, $orderDto->getShippingAddress());
    }
}
