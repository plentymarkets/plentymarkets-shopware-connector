<?php

namespace PlentyConnector\Components\AmazonPay\Validator;

use Assert\Assertion;
use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use SystemConnector\Validator\ValidatorInterface;

class AmazonPayPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PayPalInstallmentPaymentData;
    }

    /**
     * @param PayPalInstallmentPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getCurrency(), null, 'components.paypal.installment.currency');
        Assertion::notBlank($object->getCurrency(), null, 'components.paypal.installment.currency');

        Assertion::float($object->getFinancingCosts(), null, 'components.paypal.installment.financingCosts');

        Assertion::float($object->getTotalCostsIncludeFinancing(), null, 'components.paypal.installment.totalCostsIncludeFinancing');
    }
}
