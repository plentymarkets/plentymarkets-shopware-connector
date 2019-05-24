<?php

namespace SystemConnector\Validator\PaymentStatus;

use Assert\Assertion;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;
use SystemConnector\Validator\ValidatorInterface;

class PaymentStatusValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof PaymentStatus;
    }

    /**
     * @param PaymentStatus $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'paymentStatus.identifier');
        Assertion::string($object->getName(), null, 'paymentStatus.name');
        Assertion::notBlank($object->getName(), null, 'paymentStatus.name');
    }
}
