<?php

namespace SystemConnector\Validator\Product\Image;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\Validator\ValidatorInterface;

class ImageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Image;
    }

    /**
     * @param Image $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getMediaIdentifier(), null, 'product.image.mediaIdentifier');
        Assertion::isArray($object->getShopIdentifiers(), null, 'product.image.shopIdentifiers');
        Assertion::integer($object->getPosition(), null, 'product.image.position');
        Assertion::greaterOrEqualThan($object->getPosition(), 0, null, 'product.image.position');
    }
}
