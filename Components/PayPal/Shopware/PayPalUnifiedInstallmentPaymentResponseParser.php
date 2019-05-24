<?php

namespace PlentyConnector\Components\PayPal\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedInstallmentPaymentData;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalUnifiedInstallmentPaymentResponseParser implements PaymentResponseParserInterface
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
    public function parse(array $element) :array
    {
        $payments = $this->parentResponseParser->parse($element);

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $this->addPayPalUnifiedInstallment($payment, $element);
        }

        return $payments;
    }

    /**
     * @param $paymentId
     *
     * @return array|bool
     */
    private function getPayPalUnifiedInstallmentData($paymentId)
    {
        try {
            $query = 'SELECT * FROM swag_payment_paypal_unified_financing_information WHERE payment_id = ?';

            return $this->connection->fetchAssoc($query, [$paymentId]);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param Payment $payment
     * @param array   $element
     */
    private function addPayPalUnifiedInstallment(Payment $payment, array $element)
    {
        $data = $this->getPayPalUnifiedInstallmentData($element['paymentInstances'][0]['id']);

        if (empty($data)) {
            return;
        }

        $paymentData = new PayPalUnifiedInstallmentPaymentData();
        $paymentData->setFeeAmount((float) $data['fee_amount']);
        $paymentData->setTotalCost((float) $data['total_cost']);
        $paymentData->setMonthlyPayment((float) $data['monthly_payment']);
        $paymentData->setTerm((int) $data['term']);

        $payment->setPaymentData($paymentData);
    }
}
