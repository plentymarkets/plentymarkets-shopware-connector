<?php

namespace PlentyConnector\Connector\Validator\CustomerGroup;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class CustomerGroupValidator
 */
class CustomerGroupValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof CustomerGroup;
    }

    /**
     * @param CustomerGroup $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
