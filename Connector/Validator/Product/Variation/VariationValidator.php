<?php

namespace PlentyConnector\Connector\Validator\Product\Variation;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
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
        Assertion::boolean($object->getActive(), null, 'variation.active');
        Assertion::boolean($object->isMain(), null, 'variation.isMain');
        Assertion::float($object->getStock(), null, 'variation.stock');
        Assertion::string($object->getNumber(), null, 'variation.number');
        AssertioN::regex($object->getNumber(), '/^[a-zA-Z0-9-_.]+$/', null, 'variation.number');
        Assertion::integer($object->getPosition(), null, 'variation.position');
        Assertion::notBlank($object->getNumber(), null, 'variation.number');
        Assertion::allIsInstanceOf($object->getBarcodes(), Barcode::class, null, 'variation.barcodes');
        Assertion::string($object->getModel(), null, 'variation.model');
        Assertion::allIsInstanceOf($object->getImages(), Image::class, null, 'variation.images');
        Assertion::allIsInstanceOf($object->getPrices(), Price::class, null, 'variation.prices');
        Assertion::float($object->getPurchasePrice(), null, 'variation.purchasePrice');
        Assertion::nullOrUuid($object->getUnitIdentifier(), null, 'variation.unitIdentifier');
        Assertion::float($object->getContent(), null, 'variation.content');
        Assertion::float($object->getMaximumOrderQuantity(), null, 'variation.maximumOrderQuantity');
        Assertion::float($object->getMinimumOrderQuantity(), null, 'variation.minimumOrderQuantity');
        Assertion::float($object->getIntervalOrderQuantity(), null, 'variation.intervalOrderQuantity');
        Assertion::integer($object->getShippingTime(), null, 'variation.shippingTime');
        Assertion::nullOrIsInstanceOf($object->getReleaseDate(), DateTimeImmutable::class, null, 'variation.releasedate');
        Assertion::integer($object->getWidth(), null, 'variation.width');
        Assertion::integer($object->getHeight(), null, 'variation.height');
        Assertion::integer($object->getLength(), null, 'variation.length');
        Assertion::integer($object->getWeight(), null, 'variation.weight');
        Assertion::allIsInstanceOf($object->getProperties(), Property::class, null, 'variation.properties');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'variation.attributes');
    }
}
