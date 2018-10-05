<?php

namespace PlentyConnector\Connector\Validator\Identity;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Identity\Identity;

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

        Assertion::string($object->getObjectType(), null, 'identity.objectType');
        Assertion::notBlank($object->getObjectType(), null, 'identity.objectType');

        Assertion::string($object->getAdapterIdentifier(), null, 'identity.adapterIdentifier');
        Assertion::notBlank($object->getAdapterIdentifier(), null, 'identity.adapterIdentifier');

        Assertion::string($object->getAdapterName(), null, 'identity.adapterName');
        Assertion::notBlank($object->getAdapterName(), null, 'identity.adapterName');
    }
}
