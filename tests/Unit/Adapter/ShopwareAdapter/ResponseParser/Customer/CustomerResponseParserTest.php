<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use Shopware\Models\Customer\Group;
use Shopware\Models\Newsletter\Address;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParser;

/**
 * Class CustomerResponseParserTest
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

        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->expects($this->any())->method('findOneBy')->with(['key' => 'H'])->willReturn($customerGroup);

        $address = $this->createMock(Address::class);
        $address->expects($this->any())->method('getAdded')->willReturn(new \DateTime());

        $newsletterRepository = $this->createMock(EntityRepository::class);
        $newsletterRepository->expects($this->any())->method('findOneBy')->with(['email' => 'mustermann@b2b.de'])->willReturn($address);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->at(0))->method('getRepository')->willReturn($groupRepository);
        $entityManager->expects($this->at(1))->method('getRepository')->willReturn($newsletterRepository);

        /**
         * @var AddressResponseParser $parser
         */
        $this->responseParser = new CustomerResponseParser($this->identityService, $entityManager);
    }

    public function testCustomerParsing()
    {
        /**
         * @var Customer $customer
         */
        $customer = $this->responseParser->parse(self::$orderData['customer']);

        $this->assertNull($customer->getBirthday());
        $this->assertSame(Customer::TYPE_NORMAL, $customer->getType());
        $this->assertSame('mustermann@b2b.de', $customer->getEmail());
        $this->assertSame('Händler', $customer->getFirstname());
        $this->assertSame('Kundengruppe-Netto', $customer->getLastname());
        $this->assertTrue($customer->getNewsletter());
        $this->assertSame('20003', $customer->getNumber());
        $this->assertSame(Customer::SALUTATION_MR, $customer->getSalutation());
        $this->assertNull($customer->getTitle());
    }
}
