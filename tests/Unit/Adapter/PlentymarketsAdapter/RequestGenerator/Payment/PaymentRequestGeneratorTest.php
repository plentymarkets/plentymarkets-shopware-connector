<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator\Payment;

use PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\RequestGenerator\RequestGeneratorTest;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGenerator;
use Ramsey\Uuid\Uuid;
use SystemConnector\TransferObject\Payment\Payment;

class PaymentRequestGeneratorTest extends RequestGeneratorTest
{
    /**
     * @var PaymentRequestGenerator
     */
    private $requestGenerator;

    private $completeRequest = '{"amount":200,"exchangeRatio":1,"mopId":"1","currency":"1","type":"credit","transactionType":2,"status":2,"properties":[{"typeId":23,"value":4},{"typeId":11,"value":"Account Holder"},{"typeId":1,"value":"transaction"},{"typeId":3,"value":"transaction"}]}';

    protected function setUp()
    {
        parent::setUp();

        $this->requestGenerator = new PaymentRequestGenerator(
            $this->identityService
        );
    }

    /**
     * @dataProvider getPaymentObjects
     */
    public function testPaymentRequestIsGeneratedCorrectly(Payment $payment, array $expectedRequest)
    {
        $request = $this->requestGenerator->generate($payment);

        $this->assertArraySubset($expectedRequest, $request);
    }

    public function getPaymentObjects(): array
    {
        $payment = new Payment();
        $payment->setIdentifier(Uuid::uuid4()->toString());
        $payment->setOrderIdentifier(Uuid::uuid4()->toString());
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
