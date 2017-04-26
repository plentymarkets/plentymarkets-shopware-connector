<?php

namespace PlentyConnector\Payment\PayPal\Validator;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Payment\PayPal\PaymentData\PayPalInstallmentPaymentData;

/**
 * Class PayPalInstallmentPaymentDataValidator
 */
class PayPalInstallmentPaymentDataValidator implements ValidatorInterface
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
        Assertion::string($object->getCurrency(), null, 'payment.paypal.installment.currency');
        Assertion::notBlank($object->getCurrency(), null, 'payment.paypal.installment.currency');

        Assertion::float($object->getFinancingCosts(), null, 'payment.paypal.installment.financingCosts');

        Assertion::float($object->getTotalCostsIncludeFinancing(), null, 'payment.paypal.installment.totalCostsIncludeFinancing');
    }
}
