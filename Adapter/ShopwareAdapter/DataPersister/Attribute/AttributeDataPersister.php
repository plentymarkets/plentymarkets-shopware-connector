<?php

namespace ShopwareAdapter\DataPersister\Attribute;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister as ShopwareDataPersister;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Media;
use Shopware\Models\Order\Order;
use SystemConnector\ValueObject\Attribute\Attribute;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ShopwareDataPersister
     */
    private $shopwareDataPersister;

    /**
     * @var string
     */
    private $prefix = 'plenty_connector_';

    public function __construct(
        CrudService $attributeService,
        ModelManager $entityManager,
        LoggerInterface $logger,
        ShopwareDataPersister $shopwareDataPersister
    ) {
        $this->attributeService = $attributeService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
     * @param $identifier
     * @param $table
     * @param array $attributes
     */
    private function saveAttributes($identifier, $table, array $attributes = [])
    {
        try {
            Assertion::integer($identifier);
        } catch (AssertionFailedException $e) {
            $this->logger->warning($e->getMessage());
        }

        try {
            Assertion::allIsInstanceOf($attributes, Attribute::class);
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
        }

        try {
            Assertion::notBlank($table);
        } catch (AssertionFailedException $e) {
            $this->logger->warning($e->getMessage());
        }

        if (empty($attributes)) {
            return;
        }

        array_walk($attributes, function (Attribute $attribute) use ($table) {
            $this->prepareAttribute($attribute, $table);
        });

        try {
            $this->shopwareDataPersister->persist(
                $this->getAttributesAsArray($attributes),
                $table,
                $identifier
            );
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
        }
    }

    /**
     * @param Attribute $attribute
     *
     * @return string
     */
    private function getAttributeKey(Attribute $attribute): string
    {
        $key = iconv('UTF-8', 'ASCII//TRANSLIT', $attribute->getKey());

        $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));

        return $this->prefix . $attribute_key;
    }

    /**
     * @param Attribute $attribute
     * @param $table
     *
     * @throws Exception
     * @throws Exception
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
    private function getAttributesAsArray(array $attributes = []): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $key = $this->getAttributeKey($attribute);

            $result[$key] = $attribute->getValue();
        }

        return $result;
    }
}
