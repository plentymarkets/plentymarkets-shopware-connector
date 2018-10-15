<?php

namespace SystemConnector\Validator\Category;

use Assert\Assertion;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class CategoryValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Category;
    }

    /**
     * @param Category $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'category.identifier');
        Assertion::notBlank($object->getName(), null, 'category.name');
        Assertion::string($object->getName(), null, 'category.name');

        Assertion::nullOrUuid($object->getParentIdentifier(), null, 'category.parentIdentifier');
        Assertion::allUuid($object->getShopIdentifiers(), null, 'category.shopIdentifiers');
        Assertion::nullOrUuid($object->getParentIdentifier(), null, 'category.parentIdentifier');

        Assertion::allUuid($object->getImageIdentifiers(), null, 'category.imageIdentifiers');

        Assertion::integer($object->getPosition(), null, 'category.position');

        Assertion::string($object->getDescription(), null, 'category.description');
        Assertion::string($object->getLongDescription(), null, 'category.longDescription');

        Assertion::string($object->getMetaTitle(), null, 'category.metaTitle');
        Assertion::string($object->getMetaDescription(), null, 'category.metaDescription');
        Assertion::string($object->getMetaKeywords(), null, 'category.metaKeywords');
        Assertion::string($object->getMetaRobots(), null, 'category.metaRobots');
        Assertion::inArray($object->getMetaRobots(), [
            'INDEX, FOLLOW',
            'NOINDEX, FOLLOW',
            'INDEX, NOFOLLOW',
            'NOINDEX, NOFOLLOW',
        ], null, 'category.metaTobots');

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class, null, 'category.translations');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'category.attributes');
    }
}
