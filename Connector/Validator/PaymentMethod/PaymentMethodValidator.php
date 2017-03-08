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
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
