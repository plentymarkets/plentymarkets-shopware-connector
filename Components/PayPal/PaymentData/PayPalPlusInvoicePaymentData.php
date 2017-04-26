<?php

namespace PlentyConnector\Components\PayPal\PaymentData;

use DateTimeImmutable;
use PlentyConnector\Connector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class PayPalPlusInvoiceData
 */
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
    public function getReferenceNumber()
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
    public function getInstructionType()
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
    public function getBankName()
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
    public function getAccountHolderName()
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
    public function getInternationalBankAccountNumber()
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
    public function getBankIdentifierCode()
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
    public function getAmountValue()
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
    public function getAmountCurrency()
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
    public function getPaymentDueDate()
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
}
