<?php

namespace SystemConnector\Validator\OrderStatus;

use Assert\Assertion;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;
use SystemConnector\Validator\ValidatorInterface;

class OrderStatusValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof OrderStatus;
    }

    /**
     * @param OrderStatus $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'orderStatus.identifier');
        Assertion::string($object->getName(), null, 'orderStatus.name');
        Assertion::notBlank($object->getName(), null, 'orderStatus.name');
    }
}
