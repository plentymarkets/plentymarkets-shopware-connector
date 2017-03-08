<?php

namespace PlentyConnector\Connector\Validator\Identity;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Identity\Identity;

/**
 * Class IdentityValidator
 */
class IdentityValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Identity;
    }

    /**
     * @param Identity $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getObjectIdentifier());
        Assertion::string($object->getObjectType());
        Assertion::notBlank($object->getObjectType());

        Assertion::string($object->getAdapterIdentifier());
        Assertion::notBlank($object->getAdapterIdentifier());
        Assertion::string($object->getAdapterName());
        Assertion::notBlank($object->getAdapterName());
    }
}
