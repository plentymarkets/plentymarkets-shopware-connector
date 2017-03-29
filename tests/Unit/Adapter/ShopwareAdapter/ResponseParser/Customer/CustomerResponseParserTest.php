<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use Shopware\Models\Customer\Group;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParser;

/**
 * Class CustomerResponseParserTest.
 *
 * @group ResponseParser
 */
class CustomerResponseParserTest extends ResponseParserTest
{
    /**
     * @var AddressResponseParser
     */
    private $responseParser;

    public function setUp()
    {
        parent::setup();

        $customerGroup = $this->createMock(Group::class);
        $customerGroup->expects($this->any())->method('getId')->willReturn(1);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())->method('findOneBy')->with(['key' => 'H'])->willReturn($customerGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())->method('getRepository')->willReturn($repository);

        /*
         * @var AddressResponseParser $parser
         */
        $this->responseParser = new CustomerResponseParser($this->identityService, $entityManager);
    }

    public function testCustomerParsing()
    {
        /**
         * @var Customer
         */
        $customer = $this->responseParser->parse(self::$orderData['customer']);

        $this->assertNull($customer->getBirthday());
        $this->assertSame(Customer::TYPE_NORMAL, $customer->getCustomerType());
        $this->assertSame('mustermann@b2b.de', $customer->getEmail());
        $this->assertSame('Händler', $customer->getFirstname());
        $this->assertSame('Kundengruppe-Netto', $customer->getLastname());
        $this->assertFalse($customer->getNewsletter());
        $this->assertSame('20003', $customer->getNumber());
        $this->assertSame(Customer::SALUTATION_MR, $customer->getSalutation());
        $this->assertNull($customer->getTitle());
    }
}
