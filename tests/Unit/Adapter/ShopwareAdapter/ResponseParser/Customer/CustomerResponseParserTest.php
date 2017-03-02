<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Customer;

use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
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

        /**
         * @var AddressResponseParser $parser
         */
        $this->responseParser = new CustomerResponseParser($this->identityService);
    }

    public function testCustomerParsing()
    {
        /**
         * @var Customer $customer
         */
        $customer = $this->responseParser->parse(self::$orderData['customer']);

        $this->assertNull($customer->getBirthday());
        $this->assertSame(Customer::TYPE_NORMAL, $customer->getCustomerType());
        $this->assertSame('mustermann@b2b.de', $customer->getEmail());
        $this->assertSame('Händler', $customer->getFirstname());
        $this->assertSame('Kundengruppe-Netto', $customer->getLastname());
        $this->assertFalse($customer->getNewsletter());
        $this->assertSame('20003', $customer->getNumber());
        $this->assertSame('mr', $customer->getSalutation());
        $this->assertNull($customer->getTitle());
    }
}
