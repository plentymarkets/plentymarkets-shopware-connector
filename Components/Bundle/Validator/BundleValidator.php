<?php

namespace PlentyConnector\Components\Bundle\Validator;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

class BundleValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Bundle;
    }

    /**
     * @param Bundle $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'components.bundle.identifier');

        Assertion::boolean($object->isActive(), null, 'components.bundle.active');

        Assertion::uuid($object->getProductIdentifier(), null, 'components.bundle.productIdentifier');

        Assertion::string($object->getName(), null, 'components.bundle.name');
        Assertion::notBlank($object->getName(), null, 'components.bundle.name');

        Assertion::string($object->getNumber(), null, 'components.bundle.number');
        Assertion::notBlank($object->getNumber(), null, 'components.bundle.number');
        Assertion::regex($object->getNumber(), '/^[a-zA-Z0-9-_.]+$/', null, 'components.bundle.number');

        Assertion::integer($object->getPosition(), null, 'components.bundle.position');

        Assertion::integer($object->getStock(), null, 'components.bundle.stock');

        Assertion::boolean($object->hasStockLimitation(), null, 'components.bundle.stockLimitation');

        Assertion::allIsInstanceOf($object->getPrices(), Price::class, null, 'components.bundle.prices');

        Assertion::allIsInstanceOf($object->getBundleProducts(), BundleProduct::class, null, 'components.bundle.bundleProducts');

        Assertion::uuid($object->getVatRateIdentifier(), null, 'components.bundle.vatRateIdentifier');

        Assertion::nullOrIsInstanceOf($object->getAvailableFrom(), DateTimeImmutable::class, null, 'components.bundle.availableFrom');
        Assertion::nullOrIsInstanceOf($object->getAvailableTo(), DateTimeImmutable::class, null, 'components.bundle.availableTo');

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'components.bundle.translations');

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'components.bundle.attributes');
    }
}
