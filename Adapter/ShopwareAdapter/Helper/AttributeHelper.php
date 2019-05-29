<?php

namespace ShopwareAdapter\Helper;

use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValidatorService\ValidatorServiceInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class AttributeHelper implements AttributeHelperInterface
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

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

        if ($object instanceof TranslatableInterface) {
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
