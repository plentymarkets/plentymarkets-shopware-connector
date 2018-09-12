<?php

namespace PlentyConnector\Components\PayPal\Shopware;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PlentyConnector\Components\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;

class PayPalPlusInvoicePaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $parentResponseParser;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        PaymentResponseParserInterface $parentResponseParser,
        Connection $connection
    ) {
        $this->parentResponseParser = $parentResponseParser;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $element)
    {
        $payments = $this->parentResponseParser->parse($element);

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $this->addPayPalInvoiceData($payment, $element);
        }

        return $payments;
    }

    /**
     * @param string $ordernumber
     *
     * @return array|bool
     */
    private function getPayPalPlusInvoiceData($ordernumber)
    {
        try {
            $query = 'SELECT * FROM s_payment_paypal_plus_payment_instruction WHERE ordernumber = ?';

            return $this->connection->fetchAssoc($query, [$ordernumber]);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param Payment $payment
     * @param array   $element
     */
    private function addPayPalInvoiceData(Payment $payment, array $element)
    {
        $data = $this->getPayPalPlusInvoiceData($element['number']);

        if (empty($data)) {
            return;
        }

        $paymentData = new PayPalPlusInvoicePaymentData();
        $paymentData->setAmountCurrency($data['amount_currency']);
        $paymentData->setAmountValue($data['amount_value']);
        $paymentData->setBankIdentifierCode($data['bank_identifier_code']);
        $paymentData->setInternationalBankAccountNumber($data['international_bank_account_number']);
        $paymentData->setAccountHolderName($data['account_holder_name']);
        $paymentData->setBankName($data['bank_name']);
        $paymentData->setInstructionType($data['instruction_type']);
        $paymentData->setReferenceNumber($data['reference_number']);
        $paymentData->setPaymentDueDate(DateTimeImmutable::createFromFormat(
            'Y-m-d\ H:i:s',
            $data['payment_due_date']
        ));

        $payment->setPaymentData($paymentData);
    }
}
