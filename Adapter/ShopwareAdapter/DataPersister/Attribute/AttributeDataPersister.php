<?php

namespace ShopwareAdapter\DataPersister\Attribute;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister as ShopwareDataPersister;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Media;
use Shopware\Models\Order\Order;

/**
 * Class AttributeDataPersister
 */
class AttributeDataPersister implements AttributeDataPersisterInterface
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
     * @var ShopwareDataPersister
     */
    private $shopwareDataPersister;

    /**
     * @var string
     */
    private $prefix = 'plenty_connector_';

    /**
     * AttributeDataPersister constructor.
     *
     * @param CrudService           $attributeService
     * @param ModelManager          $entityManager
     * @param ShopwareDataPersister $shopwareDataPersister
     */
    public function __construct(
        CrudService $attributeService,
        ModelManager $entityManager,
        ShopwareDataPersister $shopwareDataPersister
    ) {
        $this->attributeService = $attributeService;
        $this->entityManager = $entityManager;
        $this->shopwareDataPersister = $shopwareDataPersister;
    }

    /**
     * {@inheritdoc}
     */
    public function saveCategoryAttributes(Category $category, array $attributes = [])
    {
        $this->saveAttributes($category->getId(), 's_categories_attributes', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function saveManufacturerAttributes(Supplier $supplier, array $attributes = [])
    {
        $this->saveAttributes($supplier->getId(), 's_articles_supplier_attributes', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function saveOrderAttributes(Order $order, array $attributes = [])
    {
        $this->saveAttributes($order->getId(), 's_order_attributes', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function saveMediaAttributes(Media $media, array $attributes = [])
    {
        $this->saveAttributes($media->getId(), 's_media_attributes', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function saveProductDetailAttributes(Detail $detail, array $attributes = [])
    {
        $this->saveAttributes($detail->getId(), 's_articles_attributes', $attributes);
    }

    /**
     * @param int         $identifier
     * @param string      $table
     * @param Attribute[] $attributes
     */
    private function saveAttributes($identifier, $table, array $attributes = [])
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

        $this->shopwareDataPersister->persist(
            $this->getAttributesAsArray($attributes),
            $table,
            $identifier
        );
    }

    /**
     * @param Attribute $attribute
     *
     * @return string
     */
    private function getAttributeKey(Attribute $attribute)
    {
        $key = iconv('UTF-8', 'ASCII//TRANSLIT', $attribute->getKey());

        $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));

        return $this->prefix . $attribute_key;
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

            $result[$key] = $attribute->getValue();
        }

        return $result;
    }
}
