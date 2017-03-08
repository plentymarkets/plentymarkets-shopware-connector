<?php

namespace PlentyConnector\Connector\Validator\Unit;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class UnitValidator
 */
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
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
    }
}
