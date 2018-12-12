<?php

namespace PlentyConnector\Components\PayPal\PaymentData;

use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\AbstractValueObject;

class PayPalInstallmentPaymentData extends AbstractValueObject implements PaymentDataInterface
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @var float
     */
    private $financingCosts;

    /**
     * @var float
     */
    private $totalCostsIncludeFinancing;

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return float
     */
    public function getFinancingCosts()
    {
        return $this->financingCosts;
    }

    /**
     * @param float $financingCosts
     */
    public function setFinancingCosts($financingCosts)
    {
        $this->financingCosts = $financingCosts;
    }

    /**
     * @return float
     */
    public function getTotalCostsIncludeFinancing()
    {
        return $this->totalCostsIncludeFinancing;
    }

    /**
     * @param float $totalCostsIncludeFinancing
     */
    public function setTotalCostsIncludeFinancing($totalCostsIncludeFinancing)
    {
        $this->totalCostsIncludeFinancing = $totalCostsIncludeFinancing;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'currency' => $this->getCurrency(),
            'financingCosts' => $this->getFinancingCosts(),
            'totalCostsIncludeFinancing' => $this->getTotalCostsIncludeFinancing(),
        ];
    }
}
