<?php

namespace SystemConnector\Validator\Product;

use Assert\Assertion;
use DateTimeImmutable;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\LinkedProduct\LinkedProduct;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class ProductValidator implements ValidatorInterface
{
    /**
     * @param mixed $object
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
        Assertion::uuid($object->getIdentifier(), null, 'product.identifier');

        Assertion::string($object->getName(), null, 'product.name');
        Assertion::notBlank($object->getName(), null, 'product.name');

        Assertion::string($object->getNumber(), null, 'product.number');
        Assertion::notBlank($object->getNumber(), null, 'product.number');
        Assertion::regex($object->getNumber(), '/^[a-zA-Z0-9-_.]+$/', null, 'product.number');

        Assertion::boolean($object->isActive(), null, 'product.active');

        Assertion::allUuid($object->getShopIdentifiers(), null, 'product.shopIdentifiers');

        Assertion::uuid($object->getManufacturerIdentifier(), null, 'product.manufacturerIdentifier');

        Assertion::allUuid($object->getCategoryIdentifiers(), null, 'product.categoryIdentifiers');
        Assertion::allUuid($object->getDefaultCategoryIdentifiers(), null, 'product.defaultCategoryIdentifiers');
        Assertion::allUuid($object->getShippingProfileIdentifiers(), null, 'product.name');

        Assertion::allIsInstanceOf($object->getImages(), Image::class, null, 'product.images');

        Assertion::uuid($object->getVatRateIdentifier(), null, 'product.vatRateIdentifier');

        Assertion::string($object->getDescription(), null, 'product.description');
        Assertion::string($object->getLongDescription(), null, 'product.longDescription');

        Assertion::string($object->getMetaTitle(), null, 'product.metaTitle');
        Assertion::string($object->getMetaDescription(), null, 'product.metaDescription');
        Assertion::string($object->getMetaKeywords(), null, 'product.metaKeywords');
        Assertion::string($object->getMetaRobots(), null, 'product.metaRobots');

        $allowedMetaRobots = [
            'INDEX, FOLLOW',
            'NOINDEX, FOLLOW',
            'INDEX, NOFOLLOW',
            'NOINDEX, NOFOLLOW',
        ];
        Assertion::inArray($object->getMetaRobots(), $allowedMetaRobots, null, 'product.metaRobots');

        Assertion::allIsInstanceOf($object->getLinkedProducts(), LinkedProduct::class, null, 'product.linkedProducts');

        Assertion::allUuid($object->getDocuments(), null, 'product.documents');

        Assertion::allIsInstanceOf($object->getProperties(), Property::class, null, 'product.properties');

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'product.translations');

        Assertion::nullOrIsInstanceOf($object->getAvailableFrom(), DateTimeImmutable::class, null, 'product.availableFrom');
        Assertion::nullOrIsInstanceOf($object->getAvailableTo(), DateTimeImmutable::class, null, 'product.availableTo');

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'product.attributes');

        Assertion::allIsInstanceOf($object->getVariantConfiguration(), Property::class, null, 'product.variantConfiguration');
    }
}
