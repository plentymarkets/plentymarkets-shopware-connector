<?php

namespace PlentyConnector\Components\PayPal\PaymentData;

use DateTimeImmutable;
use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class PayPalUnifiedPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $bank_name;

    /**
     * @var string
     */
    private $account_holder;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var DateTimeImmutable
     */
    private $due_date;

    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getBankName(): string
    {
        return $this->bank_name;
    }

    /**
     * @param string $bank_name
     */
    public function setBankName($bank_name)
    {
        $this->bank_name = $bank_name;
    }

    public function getAccountHolder(): string
    {
        return $this->account_holder;
    }

    /**
     * @param string $account_holder
     */
    public function setAccountHolder($account_holder)
    {
        $this->account_holder = $account_holder;
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

    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getDueDate(): DateTimeImmutable
    {
        return $this->due_date;
    }

    /**
     * @param DateTimeImmutable $due_date
     */
    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'reference' => $this->getReference(),
            'bank_name' => $this->getBankName(),
            'account_holder' => $this->getAccountHolder(),
            'iban' => $this->getIban(),
            'bic' => $this->getBic(),
            'amount' => $this->getAmount(),
            'due_date' => $this->getDueDate(),
        ];
    }
}
