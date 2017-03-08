<?php

namespace PlentyConnector\Connector\Validator\Order\Payment;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class PaymentValidator
 */
class PaymentValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Payment;
    }

    /**
     * @param Payment $object
     */
    public function validate($object)
    {
        Assertion::float($object->getAmount());
        Assertion::greaterThan($object->getAmount(), 0.0);
        Assertion::uuid($object->getCurrencyIdentifier());
        Assertion::uuid($object->getPaymentMethodIdentifier());
        Assertion::string($object->getTransactionReference());
        Assertion::notBlank($object->getTransactionReference());
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
