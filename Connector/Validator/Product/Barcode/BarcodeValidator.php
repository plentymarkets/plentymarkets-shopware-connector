<?php

namespace PlentyConnector\Connector\Validator\Product\Barcode;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class BarcodeValidator
 */
class BarcodeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
