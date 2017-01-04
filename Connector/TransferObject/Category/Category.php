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
     * Category constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string|null $parentIdentifier
     * @param string|null $shopIdentifier
     */
    public function __construct($identifier, $name, $parentIdentifier = null, $shopIdentifier = null)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::nullOrUuid($parentIdentifier);
        Assertion::nullOrUuid($shopIdentifier);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->parentIdentifier = $parentIdentifier;
        $this->shopIdentifier = $shopIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
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
}
