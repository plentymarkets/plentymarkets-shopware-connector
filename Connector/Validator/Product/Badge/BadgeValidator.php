<?php

namespace PlentyConnector\Connector\Validator\Product\Badge;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Badge\Badge;
use PlentyConnector\Connector\Validator\ValidatorInterface;

class BadgeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
