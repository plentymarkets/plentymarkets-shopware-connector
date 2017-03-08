<?php

namespace PlentyConnector\Connector\Validator\PaymentMethod;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class PaymentMethodValidator
 */
class PaymentMethodValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PaymentMethod;
    }

    /**
     * @param PaymentMethod $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'paymentMethod.identifier');
        Assertion::string($object->getName(), null, 'paymentMethod.name');
        Assertion::notBlank($object->getName(), null, 'paymentMethod.name');
    }
}
