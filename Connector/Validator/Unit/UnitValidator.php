<?php

namespace SystemConnector\Validator\Unit;

use Assert\Assertion;
use SystemConnector\TransferObject\Unit\Unit;
use SystemConnector\Validator\ValidatorInterface;

class UnitValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Unit;
    }

    /**
     * @param Unit $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'unit.identifier');
        Assertion::string($object->getName(), null, 'unit.name');
        Assertion::notBlank($object->getName(), null, 'unit.name');
    }
}
