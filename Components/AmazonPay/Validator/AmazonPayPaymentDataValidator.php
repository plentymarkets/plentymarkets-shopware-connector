<?php

namespace PlentyConnector\Components\AmazonPay\Validator;

use Assert\Assertion;
use PlentyConnector\Components\AmazonPay\PaymentData\AmazonPayPaymentData;
use SystemConnector\Validator\ValidatorInterface;

class AmazonPayPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AmazonPayPaymentData;
    }

    /**
     * @param AmazonPayPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getTransactionId(), null, 'components.amazon_pay.transaction');
        Assertion::string($object->getKey(), null, 'components.amazon_pay.key');
    }
}
