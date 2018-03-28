<?php

namespace PlentyConnector\Components\Klarna\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\Klarna\PaymentData\KlarnaPaymentData;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;

/**
 * Class KlarnaPaymentResponseParser
 */
class KlarnaPaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $parentResponseParser;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param PaymentResponseParserInterface $parentResponseParser
     * @param Connection $connection
     */
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

        $klarnaShopId = $this->getKlarnaShopId($element['number']);

        if (!$klarnaShopId) {
            return $payments;
        }

        $pClassId = $this->getKlarnaPclassId($element['number']);
        $transactionId = $this->getKlarnaTransactionId($element['number']);

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $this->addPKlarnaPaymentData($payment, $klarnaShopId, $pClassId, $transactionId);
        }

        return $payments;
    }

    /**
     * @param Payment $payment
     * @param string $klarnaShopId
     * @param string $pClassId
     * @param string $transactionId
     */
    private function addPKlarnaPaymentData(Payment $payment, $klarnaShopId, $pClassId, $transactionId)
    {
        $paymentData = new KlarnaPaymentData();
        $paymentData->setShopId($klarnaShopId);

        $payment->setPaymentData($paymentData);
    }

    /**
     * @param string $ordernumber
     * @return string|bool
     */
    private function getKlarnaShopId($ordernumber)
    {
        try {
            $query = 'SELECT eid FROM s_klarna_pclasses WHERE eid = 11700';

            return $this->connection->fetchColumn($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $ordernumber
     * @return string|bool
     */
    private function getKlarnaPclassId($ordernumber)
    {
        try {
            $query = 'SELECT pclassid FROM Pi_klarna_payment_pclass WHERE ordernumber = ?';

            return $this->connection->fetchColumn($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $ordernumber
     * @return string|bool
     */
    private function getKlarnaTransactionId($ordernumber)
    {
        try {
            $query = 'SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?';

            return $this->connection->fetchColumn($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }
}
