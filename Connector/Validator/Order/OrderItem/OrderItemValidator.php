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
        Assertion::inArray($object->getType(), $object->getTypes(), null, 'order.orderItem.type');
        Assertion::float($object->getQuantity(), null, 'order.orderItem.quantity');
        Assertion::greaterThan($object->getQuantity(), 0.0, null, 'order.orderItem.quantity');
        Assertion::string($object->getName(), null, 'order.orderItem.name');
        Assertion::notBlank($object->getName(), null, 'order.orderItem.name');
        Assertion::string($object->getNumber(), null, 'order.orderItem.number');
        Assertion::notBlank($object->getNumber(), null, 'order.orderItem.number');
        Assertion::float($object->getPrice(), null, 'order.orderItem.price');
        Assertion::nullOrUuid($object->getVatRateIdentifier(), null, 'order.orderItem.vatRateIdentifier');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'order.orderItem.attributes');
    }
}
