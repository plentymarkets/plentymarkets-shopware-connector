<?php

namespace SystemConnector\Validator\Attribute;

use Assert\Assertion;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class AttributeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Attribute;
    }

    /**
     * @param Attribute $object
     */
    public function validate($object)
    {
        Assertion::string($object->getKey(), null, 'attribute.key');
        Assertion::notBlank($object->getKey(), null, 'attribute.key');
        Assertion::string($object->getType(), null, 'attribute.type');
        Assertion::string($object->getValue(), null, 'attribute.value');
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'attribute.translations');
    }
}
