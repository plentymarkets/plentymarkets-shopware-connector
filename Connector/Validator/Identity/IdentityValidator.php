<?php

namespace SystemConnector\Validator\Identity;

use Assert\Assertion;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\Validator\ValidatorInterface;

class IdentityValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
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
