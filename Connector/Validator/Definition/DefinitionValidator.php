<?php

namespace SystemConnector\Validator\Definition;

use Assert\Assertion;
use SystemConnector\DefinitionProvider\Struct\Definition;
use SystemConnector\Validator\ValidatorInterface;

class DefinitionValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
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

        Assertion::string($object->getDestinationAdapterName(), null, 'definition.destinationAdapterName');
        Assertion::notBlank($object->getDestinationAdapterName(), null, 'definition.destinationAdapterName');

        Assertion::string($object->getObjectType(), null, 'definition.objectType');
        Assertion::notBlank($object->getObjectType(), null, 'definition.objectType');

        Assertion::integer($object->getPriority(), null, 'definition.priority');
        Assertion::greaterOrEqualThan($object->getPriority(), 0, null, 'definition.priority');

        Assertion::boolean($object->isActive(), null, 'definition.active');
    }
}
