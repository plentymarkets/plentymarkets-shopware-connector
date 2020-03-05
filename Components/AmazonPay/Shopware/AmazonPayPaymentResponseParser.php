<?php

namespace PlentyConnector\Components\AmazonPay\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\AmazonPay\PaymentData\AmazonPayPaymentData;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
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

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(
        PaymentResponseParserInterface $parentResponseParser,
        Connection $connection,
        ConfigServiceInterface $configService
    ) {
        $this->parentResponseParser = $parentResponseParser;
        $this->connection = $connection;
        $this->configService = $configService;
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
            $this->addAmazonPay($payment, $element);
        }

        return $payments;
    }

    /**
     * @param string $orderId
     */
    private function getAmazonPayData($orderId): array
    {
        try {
            $query = 'SELECT bestit_amazon_authorization_id FROM s_order_attributes WHERE orderID = ?';

            return $this->connection->fetchAssoc($query, [$orderId]);
        } catch (Exception $exception) {
            return [];
        }
    }

    private function addAmazonPay(Payment $payment, array $element)
    {
        $data = $this->getAmazonPayData($element['id']);

        if (empty($data['bestit_amazon_authorization_id']) || null === $this->configService->get('amazon_pay_key')) {
            return;
        }

        $paymentData = new AmazonPayPaymentData();
        $paymentData->setTransactionId($element['transactionId']);
        $paymentData->setKey($this->configService->get('amazon_pay_key'));

        $payment->setPaymentData($paymentData);
    }
}
