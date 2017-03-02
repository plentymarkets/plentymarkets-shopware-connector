<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
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
    /** @var  AddressResponseParser */
    private $responseParser;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setup();
        /** @var IdentityService|\PHPUnit_Framework_MockObject_MockObject $identityService */
        $identityService = $this->createMock(IdentityService::class);

        $identity = $this->createMock(Identity::class);
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn(Uuid::uuid4()->toString());
        $identityService->expects($this->any())->method('findOneOrCreate')->willReturn($identity);
        $identityService->expects($this->any())->method('findOneOrThrow')->willReturn($identity);

        /** @var AddressResponseParser $parser */
        $this->responseParser = new AddressResponseParser($identityService);
    }

    /**
     * @return void
     */
    public function testAddressParsing()
    {
        /** @var Address $address */
        $address = $this->responseParser->parse(self::$orderData['billing']);

        $this->assertInstanceOf(Attribute::class, $address->getAttributes()[0]);
        $this->assertSame('Musterstadt', $address->getCity());
        $this->assertSame('B2B', $address->getCompany());
        $this->assertSame('DEU', $address->getCountryIdentifier());
        $this->assertSame('Einkauf', $address->getDepartment());
        $this->assertSame('Händler', $address->getFirstname());
        $this->assertSame('Kundengruppe-Netto', $address->getLastname());
        $this->assertSame('mr', $address->getSalutation());
        $this->assertSame('Musterweg 1', $address->getStreet());
        $this->assertSame(null, $address->getTitle());
        $this->assertSame('', $address->getVatId());
        $this->assertSame('00000', $address->getZipcode());
    }
}