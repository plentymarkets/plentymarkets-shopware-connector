<?php

namespace SystemConnector\Validator\PaymentMethod;

use Assert\Assertion;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\Validator\ValidatorInterface;

class PaymentMethodValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
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
