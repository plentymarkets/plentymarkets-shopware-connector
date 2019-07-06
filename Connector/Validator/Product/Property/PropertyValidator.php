<?php

namespace SystemConnector\Validator\Product\Property;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Translation\Translation;

class PropertyValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Property;
    }

    /**
     * @param Property $object
     */
    public function validate($object)
    {
        Assertion::string($object->getName(), null, 'product.property.name');
        Assertion::notBlank($object->getName(), null, 'product.property.name');
        Assertion::allIsInstanceOf($object->getValues(), Value::class, null, 'product.property.values');
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'product.property.translations');
    }
}
