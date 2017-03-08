<?php

namespace PlentyConnector\Connector\Validator\Country;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class CountryValidator
 */
class CountryValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
