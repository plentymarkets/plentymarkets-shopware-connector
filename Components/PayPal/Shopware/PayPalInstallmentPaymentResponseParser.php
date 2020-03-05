<?php

namespace PlentyConnector\Components\PayPal\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalInstallmentPaymentResponseParser implements PaymentResponseParserInterface
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
    public function parse(array $element): array
    {
        $payments = $this->parentResponseParser->parse($element);

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $this->addPayPalInstallment($payment, $element);
        }

        return $payments;
    }

    /**
     * @param string $ordernumber
     *
     * @return array|bool
     */
    private function getPayPalPlusInstallmentData($ordernumber)
    {
        try {
            $query = 'SELECT * FROM s_plugin_paypal_installments_financing WHERE ordernumber = ?';

            return $this->connection->fetchAssoc($query, [$ordernumber]);
        } catch (Exception $exception) {
            return false;
        }
    }

    private function addPayPalInstallment(Payment $payment, array $element)
    {
        $data = $this->getPayPalPlusInstallmentData($element['number']);

        if (empty($data)) {
            return;
        }

        $paymentData = new PayPalInstallmentPaymentData();
        $paymentData->setCurrency($element['currency']);
        $paymentData->setFinancingCosts((float) $data['feeAmount']);
        $paymentData->setTotalCostsIncludeFinancing((float) $data['totalCost']);

        $payment->setPaymentData($paymentData);
    }
}
