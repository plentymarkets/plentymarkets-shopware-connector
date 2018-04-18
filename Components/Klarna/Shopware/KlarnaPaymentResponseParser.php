<?php

namespace PlentyConnector\Components\Klarna\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\Klarna\PaymentData\KlarnaPaymentData;
use
use PlentyConnector\Connector\TransferObject\Payment\Payment;

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

        $klarnaService = \Shopware_Plugins_Frontend_SwagPaymentKlarnaKpm_Bootstrap::class;
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

    /**
     * @param null $merchantId
     * @param null $sharedSecret
     * @return Klarna
     */
    public function getService($merchantId = null, $sharedSecret = null)
    {
        /** @var Klarna $k */
        $k = $this->Application()->KlarnaService();
        $k->setVersion($this->buildKlarnaVersion($k));

        $dbConfig = $this->Application()->Db()->getConfig();
        $k->config(
            !empty($merchantId) ? $merchantId : $this->Config()->get('merchantId'),
            !empty($sharedSecret) ? $sharedSecret : $this->Config()->get('sharedSecret'),
            KlarnaCountry::DE,
            KlarnaLanguage::DE,
            KlarnaCurrency::EUR, // Set it later
            $this->Config()->get('testDrive') ? Klarna::BETA : Klarna::LIVE,
            'pdo',
            [
                'dbTable' => 's_klarna_pclasses',
                'dbName' => $dbConfig['dbname'],
                'pdo' => $this->Application()->Db()->getConnection()
            ]
        );

        if ($this->Config()->get('testDrive')) {
            $k->setActivateInfo('flags', KlarnaFlags::TEST_MODE | KlarnaFlags::RSRV_SEND_BY_EMAIL);
        } else {
            $k->setActivateInfo('flags', KlarnaFlags::RSRV_SEND_BY_EMAIL);
        }
        return $k;
    }
}
