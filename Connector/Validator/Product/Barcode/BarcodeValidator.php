<?php

namespace SystemConnector\Validator\Product\Barcode;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Barcode\Barcode;
use SystemConnector\Validator\ValidatorInterface;

class BarcodeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Barcode;
    }

    /**
     * @param Barcode $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes(), null, 'product.barcode.type');
        Assertion::string($object->getCode(), null, 'product.barcode.code');
        Assertion::notBlank($object->getCode(), null, 'product.barcode.code');
    }
}
