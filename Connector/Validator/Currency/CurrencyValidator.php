<?php

namespace PlentyConnector\Connector\Validator\Currency;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class CurrencyValidator
 */
class CurrencyValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Currency;
    }

    /**
     * @param Currency $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
    }
}
