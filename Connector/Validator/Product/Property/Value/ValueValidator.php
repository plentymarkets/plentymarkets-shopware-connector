<?php

namespace PlentyConnector\Connector\Validator\Product\Property\Value;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

class ValueValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
