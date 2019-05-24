<?php

namespace SystemConnector\Validator\CustomerGroup;

use Assert\Assertion;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\Validator\ValidatorInterface;

class CustomerGroupValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof CustomerGroup;
    }

    /**
     * @param CustomerGroup $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'customerGroup.identifier');
        Assertion::string($object->getName(), null, 'customerGroup.name');
        Assertion::notBlank($object->getName(), null, 'customerGroup.name');
    }
}
