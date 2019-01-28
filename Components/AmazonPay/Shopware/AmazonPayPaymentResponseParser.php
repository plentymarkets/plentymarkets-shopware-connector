<?php

namespace PlentyConnector\Components\AmazonPay\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\AmazonPay\PaymentData\AmazonPayPaymentData;
use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\TransferObject\Payment\Payment;

class AmazonPayPaymentResponseParser implements PaymentResponseParserInterface
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
            $this->addAmazonPay($payment, $element);

        }
        return $payments;
    }

    /**
     * @param string $ordernumber
     *
     * @return array|bool
     */
    private function getAmazonPayData($ordernumber)
    {
        try {
            $query = 'SELECT * FROM s_order_attributes WHERE orderID = ?';

            return $this->connection->fetchAssoc($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param Payment $payment
     * @param array   $element
     */
    private function addAmazonPay(Payment $payment, array $element)
    {
        $data = $this->getAmazonPayData($element['id']);

        if (empty($data)) {
            return;
        }

        $paymentData = new AmazonPayPaymentData();
        $paymentData->setTransactionId($data['bestit_amazon_authorization_id']);

        $payment->setPaymentData($paymentData);
    }
}
