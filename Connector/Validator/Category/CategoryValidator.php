<?php

namespace PlentyConnector\Connector\Validator\Category;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class CategoryValidator
 */
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
        Assertion::uuid($object->getIdentifier());
        Assertion::notBlank($object->getName());
        Assertion::string($object->getName());

        Assertion::nullOrUuid($object->getParentIdentifier());
        Assertion::uuid($object->getShopIdentifier());

        Assertion::allUuid($object->getImageIdentifiers());

        Assertion::integer($object->getPosition());
        Assertion::greaterOrEqualThan($object->getPosition(), 0);

        Assertion::string($object->getDescription());
        Assertion::string($object->getLongDescription());

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

        Assertion::allIsInstanceOf($object->getTranslations(), Translation::class);
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
