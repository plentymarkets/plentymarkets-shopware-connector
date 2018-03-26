<?php

namespace PlentyConnector\tests\Unit\Components\PayPal\Shopware;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PlentyConnector\Components\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use PlentyConnector\Components\PayPal\Plentymarkets\PayPalPlusInvoiceRequestGenerator;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGenerator;

/**
 * Class PayPalPlusInvoiceRequestGeneratorTest
 */
class PayPalPlusInvoiceRequestGeneratorTest extends TestCase
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

        $invoiceRequestGenerator = new PayPalPlusInvoiceRequestGenerator($paymentRequestGenerator);
        $this->assertEquals($expectedResponse, $invoiceRequestGenerator->generate($payment));
    }

    public function dataProvider()
    {
        $paymentData = new PayPalPlusInvoicePaymentData();
        $paymentData->setAccountHolderName('PayPal Europe	');
        $paymentData->setAmountCurrency('EUR');
        $paymentData->setAmountValue(805.00);
        $paymentData->setBankIdentifierCode('DE36120700888000129228');
        $paymentData->setBankName('Deutsche Bank	');
        $paymentData->setInstructionType('PAY_UPON_INVOICE');
        $paymentData->setBankIdentifierCode('DEUTDEDBPAL');
        $paymentData->setPaymentDueDate(new DateTimeImmutable('2018-04-25 00:00:00'));
        $paymentData->setReferenceNumber('20224');

        $payment = new Payment();
        $payment->setPaymentData($paymentData);

        yield [
            $payment,
            [
                'properties' => [
                    [
                        'typeId' => 22,
                        'value' => json_encode([
                            'accountHolder' => $paymentData->getAccountHolderName(),
                            'bankName' => $paymentData->getBankName(),
                            'bic' => $paymentData->getBankIdentifierCode(),
                            'iban' => $paymentData->getInternationalBankAccountNumber(),
                            'paymentDue' => $paymentData->getPaymentDueDate()->format(DATE_W3C),
                            'referenceNumber' => $paymentData->getReferenceNumber(),
                        ]),
                    ],
                ],
            ],
        ];
    }
}
