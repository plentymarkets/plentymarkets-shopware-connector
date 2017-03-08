<?php

namespace PlentyConnector\Connector\Validator\ShippingProfile;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class ShippingProfileValidator
 */
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
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
