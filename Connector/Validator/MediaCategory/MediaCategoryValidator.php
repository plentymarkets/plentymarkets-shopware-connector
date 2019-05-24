<?php

namespace SystemConnector\Validator\MediaCategory;

use Assert\Assertion;
use SystemConnector\TransferObject\MediaCategory\MediaCategory;
use SystemConnector\Validator\ValidatorInterface;

class MediaCategoryValidator implements ValidatorInterface
{
    public function supports($object) :bool
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
