<?php

namespace SystemConnector\Validator\Country;

use Assert\Assertion;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\Validator\ValidatorInterface;

class CountryValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Country;
    }

    /**
     * @param Country $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
