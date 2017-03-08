<?php

namespace PlentyConnector\Connector\Validator\OrderStatus;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class OrderStatusValidator
 */
class OrderStatusValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof OrderStatus;
    }

    /**
     * @param OrderStatus $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
