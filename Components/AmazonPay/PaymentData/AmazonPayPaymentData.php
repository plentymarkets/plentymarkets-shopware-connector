<?php

namespace PlentyConnector\Components\AmazonPay\PaymentData;

use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class AmazonPayPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'currency' => $this->getTransactionId(),
        ];
    }
}
