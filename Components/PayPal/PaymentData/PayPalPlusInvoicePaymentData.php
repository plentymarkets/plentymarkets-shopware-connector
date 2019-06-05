<?php

namespace PlentyConnector\Components\PayPal\PaymentData;

use DateTimeImmutable;
use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class PayPalPlusInvoicePaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $reference_number;

    /**
     * @var string
     */
    private $instruction_type;

    /**
     * @var string
     */
    private $bank_name;

    /**
     * @var string
     */
    private $account_holder_name;

    /**
     * @var string
     */
    private $international_bank_account_number;

    /**
     * @var string
     */
    private $bank_identifier_code;

    /**
     * @var float
     */
    private $amount_value;

    /**
     * @var string
     */
    private $amount_currency;

    /**
     * @var DateTimeImmutable
     */
    private $payment_due_date;

    /**
     * @return string
     */
    public function getReferenceNumber(): string
    {
        return $this->reference_number;
    }

    /**
     * @param string $reference_number
     */
    public function setReferenceNumber($reference_number)
    {
        $this->reference_number = $reference_number;
    }

    /**
     * @return string
     */
    public function getInstructionType(): string
    {
        return $this->instruction_type;
    }

    /**
     * @param string $instruction_type
     */
    public function setInstructionType($instruction_type)
    {
        $this->instruction_type = $instruction_type;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getAccountHolderName(): string
    {
        return $this->account_holder_name;
    }

    /**
     * @param string $account_holder_name
     */
    public function setAccountHolderName($account_holder_name)
    {
        $this->account_holder_name = $account_holder_name;
    }

    /**
     * @return string
     */
    public function getInternationalBankAccountNumber(): string
    {
        return $this->international_bank_account_number;
    }

    /**
     * @param string $international_bank_account_number
     */
    public function setInternationalBankAccountNumber($international_bank_account_number)
    {
        $this->international_bank_account_number = $international_bank_account_number;
    }

    /**
     * @return string
     */
    public function getBankIdentifierCode(): string
    {
        return $this->bank_identifier_code;
    }

    /**
     * @param string $bank_identifier_code
     */
    public function setBankIdentifierCode($bank_identifier_code)
    {
        $this->bank_identifier_code = $bank_identifier_code;
    }

    /**
     * @return float
     */
    public function getAmountValue(): float
    {
        return $this->amount_value;
    }

    /**
     * @param float $amount_value
     */
    public function setAmountValue($amount_value)
    {
        $this->amount_value = $amount_value;
    }

    /**
     * @return string
     */
    public function getAmountCurrency(): string
    {
        return $this->amount_currency;
    }

    /**
     * @param string $amount_currency
     */
    public function setAmountCurrency($amount_currency)
    {
        $this->amount_currency = $amount_currency;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPaymentDueDate(): DateTimeImmutable
    {
        return $this->payment_due_date;
    }

    /**
     * @param DateTimeImmutable $payment_due_date
     */
    public function setPaymentDueDate(DateTimeImmutable $payment_due_date)
    {
        $this->payment_due_date = $payment_due_date;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'reference_number' => $this->getReferenceNumber(),
            'instruction_type' => $this->getInstructionType(),
            'bank_name' => $this->getBankName(),
            'account_holder_name' => $this->getAccountHolderName(),
            'international_bank_account_number' => $this->getInternationalBankAccountNumber(),
            'bank_identifier_code' => $this->getBankIdentifierCode(),
            'amountValue' => $this->getAmountValue(),
            'amount_currency' => $this->getAmountCurrency(),
            'payment_due_date' => $this->getPaymentDueDate(),
        ];
    }
}
