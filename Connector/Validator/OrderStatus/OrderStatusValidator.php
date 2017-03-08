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
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
    }
}
