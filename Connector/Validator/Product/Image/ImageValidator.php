<?php

namespace PlentyConnector\Connector\Validator\Product\Image;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class ImageValidator
 */
class ImageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Image;
    }

    /**
     * @param Image $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getMediaIdentifier(), null, 'image.mediaIdentifier');
        Assertion::allUuid($object->getShopIdentifiers(), null, 'image.shopIdentifiers');
        Assertion::integer($object->getPosition(), null, 'image.position');
        Assertion::greaterOrEqualThan($object->getPosition(), 0, null, 'image.position');
    }
}
