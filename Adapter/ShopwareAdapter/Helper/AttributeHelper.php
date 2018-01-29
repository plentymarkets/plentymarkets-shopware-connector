<?php

namespace ShopwareAdapter\Helper;

use PlentyConnector\Connector\TransferObject\AttributableInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class AttributeHelper
 */
class AttributeHelper implements AttributeHelperInterface
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * AttributeHelper constructor.
     *
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(ValidatorServiceInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldAsAttribute(AttributableInterface $object, $fieldName)
    {
        if (!method_exists($object, 'getAttributes')) {
            return;
        }

        $method = 'get' . ucfirst($fieldName);

        if (!method_exists($object, $method)) {
            return;
        }

        $fieldValue = $object->$method();

        if (null === $fieldValue) {
            return;
        }

        $attribute = new Attribute();
        $attribute->setKey($fieldName);
        $attribute->setValue($fieldValue);

        if ($object instanceof TranslateableInterface) {
            $translations = $object->getTranslations();
            $newTranslations = [];

            foreach ($translations as $translation) {
                if ($fieldName === $translation->getProperty()) {
                    $newTranslation = clone $translation;
                    $newTranslation->setProperty('value');

                    $newTranslations[] = $newTranslation;
                }
            }

            $attribute->setTranslations($newTranslations);
        }

        $this->validator->validate($attribute);

        $object->setAttributes(array_merge($object->getAttributes(), [$attribute]));
    }
}
