<?php

namespace PlentyConnector\Connector\Validator\Product\Property;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class PropertyValidator
 */
class PropertyValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Property;
    }

    /**
     * @param Property $object
     */
    public function validate($object)
    {
        Assertion::string($object->getName(), null, 'property.name');
        Assertion::notBlank($object->getName(), null, 'property.name');
        Assertion::allIsInstanceOf($object->getValues(), Value::class, null, 'property.values');
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'property.translations');
    }
}
