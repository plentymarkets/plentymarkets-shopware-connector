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
        Assertion::uuid($object->getObjectIdentifier(), null, 'identity.objectIdentifier');

        Assertion::string($object->getObjectType(), null, 'definition.objectType');
        Assertion::notBlank($object->getObjectType(), null, 'definition.objectType');

        Assertion::string($object->getAdapterIdentifier(), null, 'definition.adapterIdentifier');
        Assertion::notBlank($object->getAdapterIdentifier(), null, 'definition.adapterIdentifier');

        Assertion::string($object->getAdapterName(), null, 'definition.adapterName');
        Assertion::notBlank($object->getAdapterName(), null, 'definition.adapterName');
    }
}
