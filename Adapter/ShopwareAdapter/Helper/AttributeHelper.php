<?php

namespace ShopwareAdapter\Helper;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AttributableInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

/**
 * Class AttributeHelper
 */
class AttributeHelper
{
    /**
     * @var CrudService
     */
    private $attributeService;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var DataPersister
     */
    private $dataPersister;

    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * @var string
     */
    private $prefix = 'plenty_connector_';

    /**
     * AttributeHelper constructor.
     *
     * @param CrudService               $attributeService
     * @param ModelManager              $entityManager
     * @param DataPersister             $dataPersister
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(
        CrudService $attributeService,
        ModelManager $entityManager,
        DataPersister $dataPersister,
        ValidatorServiceInterface $validator
    ) {
        $this->attributeService = $attributeService;
        $this->entityManager = $entityManager;
        $this->dataPersister = $dataPersister;
        $this->validator = $validator;
    }

    /**
     * @param Attribute $attribute
     *
     * @return string
     */
    public function getAttributeKey(Attribute $attribute)
    {
        $key = iconv('UTF-8', 'ASCII//TRANSLIT', $attribute->getKey());

        $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));

        return $this->prefix . $attribute_key;
    }

    /**
     * @param int         $identifier
     * @param Attribute[] $attributes
     * @param string      $table
     */
    public function saveAttributes($identifier, array $attributes, $table)
    {
        Assertion::integer($identifier);
        Assertion::allIsInstanceOf($attributes, Attribute::class);
        Assertion::notBlank($table);

        if (empty($attributes)) {
            return;
        }

        array_walk($attributes, function (Attribute $attribute) use ($table) {
            $this->prepareAttribute($attribute, $table);
        });

        $this->dataPersister->persist(
            $this->getAttributesAsArray($attributes),
            $table,
            $identifier
        );
    }

    /**
     * @param AttributableInterface $object
     * @param $fieldName
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

    /**
     * @param Attribute $attribute
     * @param string    $table
     */
    private function prepareAttribute(Attribute $attribute, $table)
    {
        $key = $this->getAttributeKey($attribute);

        $attributeConfig = $this->attributeService->get(
            $table,
            $key
        );

        if (null === $attributeConfig) {
            $this->attributeService->update(
                $table,
                $key,
                TypeMapping::TYPE_TEXT,
                [
                    'label' => 'PlentyConnector ' . $attribute->getKey(),
                    'displayInBackend' => true,
                    'translatable' => true,
                    'custom' => true,
                ]
            );

            $this->entityManager->generateAttributeModels([$table]);
        }
    }

    /**
     * @param Attribute[] $attributes
     *
     * @return array
     */
    private function getAttributesAsArray(array $attributes = [])
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $key = $this->getAttributeKey($attribute);

            $result[$key] = (string) $attribute->getValue();
        }

        return $result;
    }
}
