<?php

namespace PlentyConnector\Connector\Validator\MediaCategory;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class MediaCategoryValidator
 */
class MediaCategoryValidator implements ValidatorInterface
{
    public function supports($object)
    {
        return $object instanceof MediaCategory;
    }

    /**
     * @param MediaCategory $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'mediaCategory.identifier');
        Assertion::string($object->getName(), null, 'mediaCategory.name');
        Assertion::notBlank($object->getName(), null, 'mediaCategory.name');
    }
}
