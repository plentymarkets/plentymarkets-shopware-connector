<?php

namespace SystemConnector\Validator\Currency;

use Assert\Assertion;
use SystemConnector\TransferObject\Currency\Currency;
use SystemConnector\Validator\ValidatorInterface;

class CurrencyValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
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
