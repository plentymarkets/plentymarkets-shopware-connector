<?php

namespace PlentyConnector\Connector\Validator\Product\Property\Value;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class ValueValidator
 */
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
        Assertion::string($object->getValue());
        Assertion::notBlank($object->getValue());
        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class);
    }
}
