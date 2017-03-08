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
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
        Assertion::allIsInstanceOf($object->getValues(), Value::class);
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class);
    }
}
