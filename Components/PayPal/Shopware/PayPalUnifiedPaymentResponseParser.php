<?php

namespace PlentyConnector\Components\PayPal\Shopware;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedPaymentData;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalUnifiedPaymentResponseParser implements PaymentResponseParserInterface
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

            $this->addPayPalUnifiedData($payment, $element);
        }

        return $payments;
    }

    /**
     * @param string $ordernumber
     *
     * @return array|bool
     */
    private function getPayPalUnifiedData($ordernumber)
    {
        try {
            $query = 'SELECT * FROM swag_payment_paypal_unified_payment_instruction WHERE ordernumber = ?';

            return $this->connection->fetchAssoc($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param Payment $payment
     * @param array   $element
     */
    private function addPayPalUnifiedData(Payment $payment, array $element)
    {
        $data = $this->getPayPalUnifiedData($element['number']);

        if (empty($data)) {
            return;
        }

        $paymentData = new PayPalUnifiedPaymentData();
        $paymentData->setAmount($data['amount']);
        $paymentData->setBic($data['bic']);
        $paymentData->setIban($data['iban']);
        $paymentData->setAccountHolder($data['account_holder']);
        $paymentData->setBankName($data['bank_name']);
        $paymentData->setReference($data['reference']);
        $paymentData->setDueDate(DateTimeImmutable::createFromFormat(
            'Y-m-d\ H:i:s',
            $data['payment_due_date']
        ));

        $payment->setPaymentData($paymentData);
    }
}
