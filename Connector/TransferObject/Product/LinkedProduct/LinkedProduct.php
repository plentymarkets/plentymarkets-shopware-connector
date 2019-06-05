<?php

namespace SystemConnector\TransferObject\Product\LinkedProduct;

use ReflectionClass;
use SystemConnector\ValueObject\AbstractValueObject;

class LinkedProduct extends AbstractValueObject
{
    const TYPE_ACCESSORY = 'accessory';
    const TYPE_REPLACEMENT = 'replacement';
    const TYPE_SIMILAR = 'similar';

    /**
     * @var string
     */
    private $type = self::TYPE_ACCESSORY;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var string
     */
    private $productIdentifier = '';

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getProductIdentifier(): string
    {
        return $this->productIdentifier;
    }

    /**
     * @param string $productIdentifier
     */
    public function setProductIdentifier($productIdentifier)
    {
        $this->productIdentifier = $productIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition(),
            'productIdentifier' => $this->getProductIdentifier(),
        ];
    }
}
