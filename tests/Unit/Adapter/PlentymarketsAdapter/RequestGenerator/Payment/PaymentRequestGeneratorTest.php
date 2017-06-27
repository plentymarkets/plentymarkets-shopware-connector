<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator\Payment;

use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator\RequestGeneratorTest;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGenerator;
use Ramsey\Uuid\Uuid;

/**
 * Class PaymentRequestGeneratorTest
 */
class PaymentRequestGeneratorTest extends RequestGeneratorTest
{
    /**
     * @var PaymentRequestGenerator
     */
    private $requestGenerator;

    private $completeRequest = '{"amount":200,"exchangeRatio":1,"mopId":"1","currency":"1","type":"credit","transactionType":2,"status":2,"properties":[{"typeId":23,"value":4},{"typeId":11,"value":"Account Holder"},{"typeId":1,"value":"transaction"},{"typeId":3,"value":"transaction"}]}';

    public function setUp()
    {
        parent::setup();

        $this->requestGenerator = $parser = new PaymentRequestGenerator(
            $this->identityService
        );
    }

    /**
     * @dataProvider getPaymentObjects
     *
     * @param Payment $payment
     * @param array   $expectedRequest
     */
    public function testPaymentRequestIsGeneratedCorrectly(Payment $payment, array $expectedRequest)
    {
        $request = $this->requestGenerator->generate($payment);

        $this->assertArraySubset($expectedRequest, $request);
    }

    public function getPaymentObjects()
    {
        $payment = new Payment();
        $payment->setIdentifier(Uuid::uuid4()->toString());
        $payment->setOrderIdentifer(Uuid::uuid4()->toString());
        $payment->setAmount(200);
        $payment->setShopIdentifier(Uuid::uuid4()->toString());
        $payment->setCurrencyIdentifier(Uuid::uuid4()->toString());
        $payment->setPaymentMethodIdentifier(Uuid::uuid4()->toString());
        $payment->setTransactionReference('transaction');
        $payment->setAccountHolder('Account Holder');

        return [
            [$payment, json_decode($this->completeRequest, true)],
        ];
    }
}
