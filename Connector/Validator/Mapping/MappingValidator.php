<?php

namespace PlentyConnector\Connector\Validator\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

/**
 * Class MappingValidator
 */
class MappingValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Mapping;
    }

    /**
     * @param Mapping $object
     */
    public function validate($object)
    {
        Assertion::string($object->getOriginAdapterName());
        Assertion::notBlank($object->getOriginAdapterName());
        Assertion::allIsInstanceOf($object->getOriginTransferObjects(), TransferObjectInterface::class);

        Assertion::string($object->getDestinationAdapterName());
        Assertion::notBlank($object->getDestinationAdapterName());
        Assertion::allIsInstanceOf($object->getDestinationTransferObjects(), TransferObjectInterface::class);
    }
}
