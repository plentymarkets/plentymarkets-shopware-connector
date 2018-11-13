<?php

namespace PlentyConnector\Components\PayPal\PaymentData;

use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class PayPalUnifiedInstallmentPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var float
     */
    private $fee_amount;

    /**
     * @var float
     */
    private $total_cost;

    /**
     * @var int
     */
    private $term;

    /**
     * @var float
     */
    private $monthly_payment;

    /**
     * @return float
     */
    public function getFeeAmount()
    {
        return $this->fee_amount;
    }

    /**
     * @param float $fee_amount
     */
    public function setFeeAmount($fee_amount)
    {
        $this->fee_amount = $fee_amount;
    }

    /**
     * @return float
     */
    public function getTotalCost()
    {
        return $this->total_cost;
    }

    /**
     * @param float $total_cost
     */
    public function setTotalCost($total_cost)
    {
        $this->total_cost = $total_cost;
    }

    /**
     * @return int
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param int $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * @return float
     */
    public function getMonthlyPayment()
    {
        return $this->monthly_payment;
    }

    /**
     * @param float $monthly_payment
     */
    public function setMonthlyPayment($monthly_payment)
    {
        $this->monthly_payment = $monthly_payment;
    }
}
