<?php

namespace PlentyConnector\Connector\Validator\Order\OrderItem;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class OrderItemValidator
 */
class OrderItemValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof OrderItem;
    }

    /**
     * @param OrderItem $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes());
        Assertion::float($object->getQuantity());
        Assertion::greaterThan($object->getQuantity(), 0.0);
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
        Assertion::string($object->getNumber());
        Assertion::notBlank($object->getNumber());
        Assertion::float($object->getPrice());
        Assertion::uuid($object->getVatRateIdentifier());
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
