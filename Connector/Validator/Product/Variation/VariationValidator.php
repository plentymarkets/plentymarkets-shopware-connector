<?php

namespace SystemConnector\Validator\Product\Variation;

use Assert\Assertion;
use DateTimeImmutable;
use SystemConnector\TransferObject\Product\Barcode\Barcode;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Variation\Variation;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class VariationValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Variation;
    }

    /**
     * @param Variation $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'product.variation.identifier');
        Assertion::uuid($object->getProductIdentifier(), null, 'product.variation.productIdentifier');
        Assertion::boolean($object->getActive(), null, 'product.variation.active');
        Assertion::boolean($object->isMain(), null, 'product.variation.isMain');
        Assertion::string($object->getNumber(), null, 'product.variation.number');
        Assertion::regex($object->getNumber(), '/^[a-zA-Z0-9-_.]+$/', null, 'product.variation.number');
        Assertion::notBlank($object->getNumber(), null, 'product.variation.number');
        Assertion::regex($object->getNumber(), '/^[a-zA-Z0-9-_.]+$/', null, 'product.variation.number');
        Assertion::integer($object->getPosition(), null, 'product.variation.position');
        Assertion::notBlank($object->getNumber(), null, 'product.variation.number');
        Assertion::allIsInstanceOf($object->getBarcodes(), Barcode::class, null, 'product.variation.barcodes');
        Assertion::string($object->getModel(), null, 'product.variation.model');
        Assertion::allIsInstanceOf($object->getImages(), Image::class, null, 'product.variation.images');
        Assertion::allIsInstanceOf($object->getPrices(), Price::class, null, 'product.variation.prices');
        Assertion::float($object->getPurchasePrice(), null, 'product.variation.purchasePrice');
        Assertion::float($object->getContent(), null, 'product.variation.content');
        Assertion::nullOrUuid($object->getUnitIdentifier(), null, 'product.variation.unitIdentifier');
        Assertion::float($object->getReferenceAmount(), null, 'product.variation.referenceAmount');
        Assertion::boolean($object->hasStockLimitation(), null, 'product.variation.stockLimitation');
        Assertion::float($object->getMaximumOrderQuantity(), null, 'product.variation.maximumOrderQuantity');
        Assertion::float($object->getMinimumOrderQuantity(), null, 'product.variation.minimumOrderQuantity');
        Assertion::float($object->getIntervalOrderQuantity(), null, 'product.variation.intervalOrderQuantity');
        Assertion::integer($object->getShippingTime(), null, 'product.variation.shippingTime');
        Assertion::nullOrIsInstanceOf($object->getReleaseDate(), DateTimeImmutable::class, null, 'product.variation.releasedate');
        Assertion::integer($object->getWidth(), null, 'product.variation.width');
        Assertion::integer($object->getHeight(), null, 'product.variation.height');
        Assertion::integer($object->getLength(), null, 'product.variation.length');
        Assertion::float($object->getWeight(), null, 'product.variation.weight');
        Assertion::allIsInstanceOf($object->getProperties(), Property::class, null, 'product.variation.properties');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'product.variation.attributes');
    }
}
