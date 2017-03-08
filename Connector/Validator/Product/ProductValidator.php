<?php

namespace PlentyConnector\Connector\Validator\Product;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class ProductValidator
 */
class ProductValidator implements ValidatorInterface
{
    /**
     * @param $object
     *
     * @return bool
     */
    public function supports($object)
    {
        return $object instanceof Product;
    }

    /**
     * @param Product $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());

        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());

        Assertion::string($object->getNumber());
        Assertion::notBlank($object->getNumber());

        Assertion::boolean($object->getActive());

        Assertion::allUuid($object->getShopIdentifiers());

        Assertion::uuid($object->getManufacturerIdentifier());

        Assertion::allUuid($object->getCategoryIdentifiers());
        Assertion::allUuid($object->getDefaultCategoryIdentifiers());
        Assertion::allUuid($object->getShippingProfileIdentifiers());
        Assertion::allUuid($object->getImageIdentifiers());

        Assertion::allIsInstanceOf($object->getVariations(), Variation::class);

        Assertion::uuid($object->getVatRateIdentifier());

        Assertion::boolean($object->getLimitedStock());

        Assertion::string($object->getDescription());
        Assertion::string($object->getLongDescription());
        Assertion::string($object->getTechnicalDescription());

        Assertion::string($object->getMetaTitle());
        Assertion::string($object->getMetaDescription());
        Assertion::string($object->getMetaKeywords());
        Assertion::string($object->getMetaRobots());
        Assertion::inArray($object->getMetaRobots(), [
            'INDEX, FOLLOW',
            'NOINDEX, FOLLOW',
            'INDEX, NOFOLLOW',
            'NOINDEX, NOFOLLOW',
        ]);

        Assertion::allIsInstanceOf($object->getLinkedProducts(), LinkedProduct::class);

        Assertion::allUuid($object->getDocuments());

        Assertion::allIsInstanceOf($object->getProperties(), Property::class);

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class);

        Assertion::nullOrIsInstanceOf($object->getAvailableFrom(), \DateTimeImmutable::class);
        Assertion::nullOrIsInstanceOf($object->getAvailableTo(), \DateTimeImmutable::class);

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
