<?php

namespace PlentyConnector\Components\Sepa\PaymentData;

use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

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

    public function getAccountOwner(): string
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

    public function getIban(): string
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'accountOwner' => $this->getAccountOwner(),
            'iban' => $this->getIban(),
            'bic' => $this->getBic(),
        ];
    }
}
