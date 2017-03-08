<?php

namespace PlentyConnector\Connector\Validator\Product\Variation;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class VariationValidator
 */
class VariationValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Variation;
    }

    /**
     * @param Variation $object
     */
    public function validate($object)
    {
        Assertion::boolean($object->getActive());
        Assertion::boolean($object->isMain());
        Assertion::float($object->getStock());
        Assertion::greaterOrEqualThan($object->getStock(), 0.0);
        Assertion::string($object->getNumber());
        Assertion::notBlank($object->getNumber());
        Assertion::allIsInstanceOf($object->getBarcodes(), Barcode::class);
        Assertion::string($object->getModel());
        Assertion::allUuid($object->getImageIdentifiers());
        Assertion::allIsInstanceOf($object->getPrices(), Price::class);
        Assertion::float($object->getPurchasePrice());
        Assertion::uuid($object->getUnitIdentifier());
        Assertion::float($object->getContent());
        Assertion::float($object->getMaximumOrderQuantity());
        Assertion::float($object->getMinimumOrderQuantity());
        Assertion::float($object->getIntervalOrderQuantity());
        Assertion::integer($object->getShippingTime());
        Assertion::isInstanceOf($object->getReleaseDate(), \DateTimeImmutable::class);
        Assertion::integer($object->getWidth());
        Assertion::integer($object->getHeight());
        Assertion::integer($object->getLength());
        Assertion::integer($object->getWeight());
        Assertion::isInstanceOf($object->getProperties(), Property::class);
        Assertion::isInstanceOf($object->getAttributes(), Attribute::class);
    }
}
