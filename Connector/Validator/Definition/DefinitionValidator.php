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
        Assertion::string($object->getOriginAdapterName(), null, 'definition.originAdapterName');
        Assertion::notBlank($object->getOriginAdapterName(), null, 'definition.originAdapterName');

        Assertion::string($object->getDestinationAdapterName(), null, 'definition.destionationAdapterName');
        Assertion::notBlank($object->getDestinationAdapterName(), null, 'definition.destionationAdapterName');

        Assertion::string($object->getObjectType(), null, 'definition.objectType');
        Assertion::notBlank($object->getObjectType(), null, 'definition.objectType');

        Assertion::integer($object->getPriority(), null, 'definition.priority');
        Assertion::greaterOrEqualThan($object->getPriority(), 0, null, 'definition.priority');

        Assertion::boolean($object->isActive(), null, 'definition.active');
    }
}
