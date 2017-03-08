<?php

namespace PlentyConnector\Connector\Validator\Product\LinkedProduct;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class LinkedProductValidator
 */
class LinkedProductValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof LinkedProduct;
    }

    /**
     * @param LinkedProduct $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes());
        Assertion::integer($object->getPosition());
        Assertion::greaterOrEqualThan($object->getPosition(), 0);
        Assertion::uuid($object->getProductIdentifier());
    }
}
