<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use Ramsey\Uuid\Uuid;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;

/**
 * Class AddressResponseParserTest
 *
 * @group ResponseParser
 */
class AddressResponseParserTest extends ResponseParserTest
{
    /**
     * @var AddressResponseParser
     */
    private $responseParser;

    /**
     * @var string
     */
    private $countyIdentifier;

    protected function setUp()
    {
        parent::setUp();

        $this->countyIdentifier = Uuid::uuid4()->toString();

        $identity = $this->createMock(Identity::class);
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn($this->countyIdentifier);

        /**
         * @var IdentityService|\PHPUnit_Framework_MockObject_MockObject $identityService
         */
        $identityService = $this->createMock(IdentityService::class);
        $identityService->expects($this->any())->method('findOneBy')->willReturn($identity);

        /**
         * @var AddressResponseParser $parser
         */
        $this->responseParser = new AddressResponseParser($identityService);
    }

    public function testAddressParsing()
    {
        /**
         * @var Address $address
         */
        $address = $this->responseParser->parse(self::$orderData['billing']);

        self::assertInstanceOf(Attribute::class, $address->getAttributes()[0]);
        self::assertSame('Musterstadt', $address->getCity());
        self::assertSame('B2B', $address->getCompany());
        self::assertSame($this->countyIdentifier, $address->getCountryIdentifier());
        self::assertSame('Einkauf', $address->getDepartment());
        self::assertSame('Händler', $address->getFirstname());
        self::assertSame('Kundengruppe-Netto', $address->getLastname());
        self::assertSame(Customer::GENDER_MALE, $address->getGender());
        self::assertSame('Musterweg 1', $address->getStreet());
        self::assertNull($address->getTitle());
        self::assertNull($address->getVatId());
        self::assertSame('00000', $address->getPostalCode());
    }
}
