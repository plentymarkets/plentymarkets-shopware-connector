<?php

namespace SystemConnector\Validator\Manufacturer;

use Assert\Assertion;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class ManufacturerValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
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
