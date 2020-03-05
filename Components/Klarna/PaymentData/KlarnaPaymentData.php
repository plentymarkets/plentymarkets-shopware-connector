<?php

namespace PlentyConnector\Components\Klarna\PaymentData;

use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class KlarnaPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $pclassId;

    /**
     * @var string
     */
    private $transactionId;

    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    public function getPclassId(): string
    {
        return $this->pclassId;
    }

    /**
     * @param string $pclassId
     */
    public function setPclassId($pclassId)
    {
        $this->pclassId = $pclassId;
    }

    public function getTransactionId(): string
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
            'shopId' => $this->getShopId(),
            'pclassId' => $this->getPclassId(),
            'transactionId' => $this->getTransactionId(),
        ];
    }
}
