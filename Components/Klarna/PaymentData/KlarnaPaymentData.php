<?php

namespace PlentyConnector\Components\Klarna\PaymentData;

use PlentyConnector\Connector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class SepaPaymentData
 */
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

    /**
     * @return string
     */
    public function getShopId()
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

    /**
     * @return string
     */
    public function getPclassId()
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
}
