<?php

namespace SystemConnector\Validator\Media;

use Assert\Assertion;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

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
        Assertion::uuid($object->getIdentifier(), null, 'media.identifier');

        Assertion::uuid($object->getMediaCategoryIdentifier(), null, 'media.mediaCategoryIdentifier');

        Assertion::url($object->getLink(), null, 'media.content');

        Assertion::nullOrNotBlank($object->getName(), null, 'media.name');
        Assertion::nullOrNotBlank($object->getAlternateName(), null, 'media.name');

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'media.translations');

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'media.attributes');
    }
}
