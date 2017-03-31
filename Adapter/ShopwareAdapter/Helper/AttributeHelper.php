<?php

namespace PlentyConnector\Adapter\ShopwareAdapter\Helper;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
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
     * @var string
     */
    private $prefix = 'plenty_connector_';

    /**
     * AttributeHelper constructor.
     *
     * @param CrudService $attributeService
     * @param ModelManager $entityManager
     * @param DataPersister $dataPersister
     */
    public function __construct(
        CrudService $attributeService,
        ModelManager $entityManager,
        DataPersister $dataPersister
    ) {
        $this->attributeService = $attributeService;
        $this->entityManager = $entityManager;
        $this->dataPersister = $dataPersister;
    }

    /**
     * @param Attribute $attribute
     *
     * @return string
     */
    public function getAttributeKey(Attribute $attribute)
    {
        $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($attribute->getKey())));

        return $this->prefix . $attribute_key;
    }

    /**
     * @param Identity $identity
     * @param Attribute[] $attributes
     * @param string $table
     */
    public function saveAttributes(Identity $identity, array $attributes, $table)
    {
        Assertion::allIsInstanceOf($attributes, Attribute::class);
        Assertion::notBlank($table);

        array_walk($attributes, function (Attribute $attribute) use ($table) {
            $this->prepareAttribute($attribute, $table);
        });

        $this->dataPersister->persist(
            $this->getAttributesAsArray($attributes),
            $table,
            $identity->getAdapterIdentifier()
        );
    }

    /**
     * @param Attribute $attribute
     * @param string $table
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
                TypeMapping::TYPE_STRING,
                [
                    'label' => 'PlentyConnector ' . $key,
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
