<?php

namespace PlentyConnector\Connector\TransferObject\Order\PaymentData;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class SepaPaymentData
 */
class SepaPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $accountOwner;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var null|string
     */
    private $bic;

    /**
     * @return string
     */
    public function getAccountOwner()
    {
        return $this->accountOwner;
    }

    /**
     * @param string $accountOwner
     */
    public function setAccountOwner($accountOwner)
    {
        $this->accountOwner = $accountOwner;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return null|string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param null|string $bic
     */
    public function setBic($bic = null)
    {
        $this->bic = $bic;
    }
}
