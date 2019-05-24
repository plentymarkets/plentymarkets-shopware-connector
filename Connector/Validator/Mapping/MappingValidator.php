<?php

namespace SystemConnector\Validator\Mapping;

use Assert\Assertion;
use SystemConnector\MappingService\Struct\Mapping;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\Validator\ValidatorInterface;

class MappingValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Mapping;
    }

    /**
     * @param Mapping $object
     */
    public function validate($object)
    {
        Assertion::string($object->getOriginAdapterName(), null, 'mapping.originAdapterName');
        Assertion::notBlank($object->getOriginAdapterName(), null, 'mapping.originAdapterName');
        Assertion::allIsInstanceOf($object->getOriginTransferObjects(), TransferObjectInterface::class, null, 'mapping.originTransferObjects');

        Assertion::string($object->getDestinationAdapterName(), null, 'mapping.destinationAdapterName');
        Assertion::notBlank($object->getDestinationAdapterName(), null, 'mapping.destinationAdapterName');
        Assertion::allIsInstanceOf($object->getDestinationTransferObjects(), TransferObjectInterface::class, null, 'mapping.destinationTransferObjects');
    }
}
