<?php

namespace SystemConnector\Validator\ShippingProfile;

use Assert\Assertion;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;
use SystemConnector\Validator\ValidatorInterface;

class ShippingProfileValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ShippingProfile;
    }

    /**
     * @param ShippingProfile $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'shippingProfile.identifier');
        Assertion::string($object->getName(), null, 'shippingProfile.name');
        Assertion::notBlank($object->getName(), null, 'shippingProfile.name');
    }
}
