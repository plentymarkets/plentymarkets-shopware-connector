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
        Assertion::uuid($object->getIdentifier(), null, 'currency.identifier');
        Assertion::string($object->getName(), null, 'currency.name');
        Assertion::notBlank($object->getName(), null, 'currency.name');
    }
}
