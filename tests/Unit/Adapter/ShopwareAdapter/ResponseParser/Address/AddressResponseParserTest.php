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

    public function setUp()
    {
        parent::setup();

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

        $this->assertInstanceOf(Attribute::class, $address->getAttributes()[0]);
        $this->assertSame('Musterstadt', $address->getCity());
        $this->assertSame('B2B', $address->getCompany());
        $this->assertSame($this->countyIdentifier, $address->getCountryIdentifier());
        $this->assertSame('Einkauf', $address->getDepartment());
        $this->assertSame('Händler', $address->getFirstname());
        $this->assertSame('Kundengruppe-Netto', $address->getLastname());
        $this->assertSame(Customer::SALUTATION_MR, $address->getSalutation());
        $this->assertSame('Musterweg 1', $address->getStreet());
        $this->assertNull($address->getTitle());
        $this->assertNull($address->getVatId());
        $this->assertSame('00000', $address->getPostalCode());
    }
}
