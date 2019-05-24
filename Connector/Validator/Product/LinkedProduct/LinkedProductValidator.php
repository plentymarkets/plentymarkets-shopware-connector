<?php

namespace SystemConnector\Validator\Product\LinkedProduct;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\LinkedProduct\LinkedProduct;
use SystemConnector\Validator\ValidatorInterface;

class LinkedProductValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof LinkedProduct;
    }

    /**
     * @param LinkedProduct $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes(), null, 'product.linkedProduct.type');
        Assertion::integer($object->getPosition(), null, 'product.linkedProduct.position');
        Assertion::greaterOrEqualThan($object->getPosition(), 0, null, 'product.linkedProduct.position');
        Assertion::uuid($object->getProductIdentifier(), null, 'product.linkedProduct.productIdentifier');
    }
}
