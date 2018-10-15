<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\Payment;

use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use ShopwareAdapter\DataProvider\Currency\CurrencyDataProviderInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParser;
use SystemConnector\Validator\Order\Payment\PaymentValidator;

class PaymentResponseParserTest extends ResponseParserTest
{
    /**
     * @var PaymentResponseParser
     */
    private $responseParser;

    /**
     * @var PaymentValidator
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $currencyDataProvider = $this->createMock(CurrencyDataProviderInterface::class);

        $this->responseParser = new PaymentResponseParser(
            $this->identityService,
            $currencyDataProvider
        );

        $this->validator = new PaymentValidator();
    }

    public function testPayedOrderGeneratesPaymentTransferObject()
    {
        $payments = $this->responseParser->parse(self::$orderData);

        $this->assertCount(0, $payments);
    }
}
