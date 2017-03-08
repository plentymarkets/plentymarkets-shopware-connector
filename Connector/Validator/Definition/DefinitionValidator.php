<?php

namespace PlentyConnector\Connector\Validator\Definition;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;

/**
 * Class DefinitionValidator
 */
class DefinitionValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Definition;
    }

    /**
     * @param Definition $object
     */
    public function validate($object)
    {
        Assertion::string($object->getOriginAdapterName());
        Assertion::notBlank($object->getOriginAdapterName());
        Assertion::string($object->getDestinationAdapterName());
        Assertion::notBlank($object->getDestinationAdapterName());
        Assertion::string($object->getObjectType());
        Assertion::notBlank($object->getObjectType());
        Assertion::integer($object->getPriority());
        Assertion::greaterOrEqualThan($object->getPriority(), 0);
        Assertion::boolean($object->isActive());
    }
}
