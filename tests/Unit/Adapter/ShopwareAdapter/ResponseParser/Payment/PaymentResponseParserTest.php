<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\Validator\Order\Payment\PaymentValidator;
use PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser\ResponseParserTest;
use ShopwareAdapter\DataProvider\Currency\CurrencyDataProviderInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParser;

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

    public function setUp()
    {
        parent::setup();

        $currencyDataProvider = $this->createMock(CurrencyDataProviderInterface::class);
        $currencyDataProvider->expects($this->once())->method('getCurrencyIdentifierByCode')->willReturn('1');

        $this->responseParser = $parser = new PaymentResponseParser(
            $this->identityService,
            $currencyDataProvider
        );

        $this->validator = new PaymentValidator();
    }

    public function testPayedOrderGeneratesPaymentTransferObject()
    {
        $payments = $this->responseParser->parse(self::$orderData);

        $this->assertCount(1, $payments);

        /**
         * @var Payment $payment
         */
        $payment = array_shift($payments);

        self::assertInstanceOf(Payment::class, $payment);
        self::assertEquals(998.56, $payment->getAmount());
        self::assertEquals('transactionId', $payment->getTransactionReference());

        $this->validator->validate($payment);
    }
}
