<?php

namespace PlentyConnector\Connector\Validator\Manufacturer;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class ManufacturerValidator
 */
class ManufacturerValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Manufacturer;
    }

    /**
     * @param Manufacturer $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'manufacturer.identifier');

        Assertion::string($object->getName(), null, 'manufacturer.name');
        Assertion::notBlank($object->getName(), null, 'manufacturer.name');

        Assertion::nullOrUuid($object->getLogoIdentifier(), null, 'manufacturer.logoIdentifier');

        Assertion::nullOrNotBlank($object->getLink(), null, 'manufacturer.link');

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'manufacturer.attributes');
    }
}
