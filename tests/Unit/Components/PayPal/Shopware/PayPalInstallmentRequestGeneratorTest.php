<?php

namespace PlentyConnector\tests\Unit\Components\PayPal\Shopware;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use PlentyConnector\Components\PayPal\Plentymarkets\PayPalInstallmentRequestGenerator;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGenerator;

/**
 * Class PayPalPlusInstallmentRequestGeneratorTest
 */
class PayPalInstallmentRequestGeneratorTest extends TestCase
{
    /**
     * @param Payment $payment
     * @param array   $expectedResponse
     *
     * @dataProvider dataProvider
     */
    public function testGeneratePayPalPlusInvoicePaymentRequest(Payment $payment, array $expectedResponse)
    {
        $paymentRequestGenerator = $this->createMock(PaymentRequestGenerator::class);
        $paymentRequestGenerator->expects($this->once())->method('generate')->with($payment)->willReturn([]);

        $invoiceRequestGenerator = new PayPalInstallmentRequestGenerator($paymentRequestGenerator);
        $this->assertEquals($expectedResponse, $invoiceRequestGenerator->generate($payment));
    }

    public function dataProvider()
    {
        $paymentData = new PayPalInstallmentPaymentData();
        $paymentData->setCurrency('EUR');
        $paymentData->setFinancingCosts(19.95);
        $paymentData->setTotalCostsIncludeFinancing(100);

        $payment = new Payment();
        $payment->setPaymentData($paymentData);

        yield [
            $payment,
            [
                'properties' => [
                    [
                        'typeId' => 22,
                        'value' => json_encode([
                            'currency' => $paymentData->getCurrency(),
                            'financingCosts' => $paymentData->getFinancingCosts(),
                            'totalCostsIncludeFinancing' => $paymentData->getTotalCostsIncludeFinancing(),
                        ]),
                    ],
                ],
            ],
        ];
    }
}
