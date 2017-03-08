<?php

namespace PlentyConnector\Connector\Validator\Media;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class MediaValidator
 */
class MediaValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Media;
    }

    /**
     * @param Media $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());
        Assertion::uuid($object->getMediaCategoryIdentifier());
        Assertion::url($object->getLink());
        Assertion::nullOrString($object->getName());
        Assertion::nullOrString($object->getAlternateName());
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class);
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
