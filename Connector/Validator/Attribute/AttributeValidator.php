<?php

namespace PlentyConnector\Connector\Validator\Attribute;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class AttributeValidator
 */
class AttributeValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
        Assertion::string($object->getValue(), null, 'attribute.value');
        Assertion::notBlank($object->getValue(), null, 'attribute.value');
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'attribute.translations');
    }
}
