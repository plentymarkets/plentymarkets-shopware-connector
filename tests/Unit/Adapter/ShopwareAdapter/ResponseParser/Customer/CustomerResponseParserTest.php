<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Customer;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use Psr\Log\LoggerInterface;
use Shopware\Models\Customer\Group;
use Shopware\Models\Newsletter\Address;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParser;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParserInterface;

/**
 * Class CustomerResponseParserTest
 *
 * @group ResponseParser
 */
class CustomerResponseParserTest extends ResponseParserTest
{
    /**
     * @var CustomerResponseParserInterface
     */
    private $responseParser;

    protected function setUp()
    {
        parent::setUp();

        $customerGroup = $this->createMock(Group::class);
        $customerGroup->expects($this->any())->method('getId')->willReturn(1);

        $groupRepository = $this->createMock(ObjectRepository::class);
        $groupRepository->expects($this->any())->method('findOneBy')->with(['key' => 'H'])->willReturn($customerGroup);

        $address = $this->createMock(Address::class);
        $address->expects($this->any())->method('getAdded')->willReturn(new \DateTime());

        $newsletterRepository = $this->createMock(ObjectRepository::class);
        $newsletterRepository->expects($this->any())->method('findOneBy')->with(['email' => 'mustermann@b2b.de'])->willReturn($address);

        $locale = $this->createMock(Locale::class);
        $locale->expects($this->any())->method('getId')->willReturn(1);

        $shop = $this->createMock(Shop::class);
        $shop->expects($this->any())->method('getLocale')->willReturn($locale);

        $shopRepository = $this->createMock(ObjectRepository::class);
        $shopRepository->expects($this->any())->method('find')->willReturn($shop);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->at(0))->method('getRepository')->willReturn($shopRepository);
        $entityManager->expects($this->at(1))->method('getRepository')->willReturn($groupRepository);
        $entityManager->expects($this->at(2))->method('getRepository')->willReturn($newsletterRepository);

        $logger = $this->createMock(LoggerInterface::class);

        /**
         * @var AddressResponseParser $parser
         */
        $this->responseParser = new CustomerResponseParser($this->identityService, $entityManager, $logger);
    }

    public function testCustomerParsing()
    {
        /**
         * @var Customer|null $customer
         */
        $customer = $this->responseParser->parse(self::$orderData['customer']);

        self::assertNotNull($customer);

        if (null === $customer) {
            return;
        }

        self::assertNull($customer->getBirthday());
        self::assertSame(Customer::TYPE_NORMAL, $customer->getType());
        self::assertSame('mustermann@b2b.de', $customer->getEmail());
        self::assertSame('Händler', $customer->getFirstname());
        self::assertSame('Kundengruppe-Netto', $customer->getLastname());
        self::assertTrue($customer->getNewsletter());
        self::assertSame('20003', $customer->getNumber());
        self::assertSame(Customer::SALUTATION_MR, $customer->getSalutation());
        self::assertNull($customer->getTitle());
    }
}
