<?php

namespace PlentyConnector\Connector\Validator\PaymentStatus;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\Validator\ValidatorInterface;

class PaymentStatusValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
