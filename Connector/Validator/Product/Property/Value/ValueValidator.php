<?php

namespace SystemConnector\Validator\Product\Property\Value;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Translation\Translation;

class ValueValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Value;
    }

    /**
     * @param Value $object
     */
    public function validate($object)
    {
        Assertion::string($object->getValue(), null, 'product.property.value.value');
        Assertion::notBlank($object->getValue(), null, 'product.property.value.value');
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'product.property.value.translations');
    }
}
