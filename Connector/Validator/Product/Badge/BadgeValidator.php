<?php

namespace SystemConnector\Validator\Product\Badge;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Badge\Badge;
use SystemConnector\Validator\ValidatorInterface;

class BadgeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Badge;
    }

    /**
     * @param Badge $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes(), null, 'product.badge.type');
    }
}
