<?php

namespace PlentyConnector\Connector\TransferObject\Category;

use Assert\Assertion;

/**
 * Class Category
 */
class Category implements CategoryInterface
{
    const TYPE = 'Category';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $parentIdentifier;

    /**
     * @var string
     */
    private $shopIdentifier;

    /**
     * @var integer
     */
    private $position;

    /**
     * Category constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string|null $parentIdentifier
     * @param string $shopIdentifier
     * @param integer $position
     */
    public function __construct(
        $identifier,
        $name,
        $parentIdentifier = null,
        $shopIdentifier,
        $position,
        $description,
        $longDescription,
        $metaTitle,
        $metaDescription,
        $metaKeywords,
        $translations,
        $attributes
    ) {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::nullOrUuid($parentIdentifier);
        Assertion::uuid($shopIdentifier);
        Assertion::integer($position);
        Assertion::greaterOrEqualThan($position, 0);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->parentIdentifier = $parentIdentifier;
        $this->shopIdentifier = $shopIdentifier;
        $this->position = $position;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'parentIdentifier',
            'shopIdentifier',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['parentIdentifier'],
            $params['shopIdentifier']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * @return string
     */
    public function getShopIdentifier()
    {
        return $this->shopIdentifier;
    }

    /**
     * @return TranslationInterface[]
     */
    public function getTranslations()
    {
        // TODO: Implement getTranslations() method.
    }
}
