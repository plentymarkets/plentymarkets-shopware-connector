<?php

namespace PlentyConnector\Components\PayPal\Validator;

use Assert\Assertion;
use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedInstallmentPaymentData;
use SystemConnector\Validator\ValidatorInterface;

class PayPalUnifiedInstallmentPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof PayPalUnifiedInstallmentPaymentData;
    }

    /**
     * @param PayPalUnifiedInstallmentPaymentData $object
     */
    public function validate($object)
    {
        Assertion::float($object->getFeeAmount(), null, 'components.paypal.unified.installment.feeAmount');

        Assertion::float($object->getTotalCost(), null, 'components.paypal.unified.installment.totalCost');

        Assertion::integer($object->getTerm(), null, 'components.paypal.unified.installment.term');

        Assertion::float($object->getMonthlyPayment(), null, 'components.paypal.unified.installment.monthlyPayment');
    }
}
