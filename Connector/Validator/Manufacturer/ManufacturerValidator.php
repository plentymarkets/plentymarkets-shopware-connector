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
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
        Assertion::nullOrUuid($object->getLogoIdentifier());
        Assertion::nullOrString($object->getLink());
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
