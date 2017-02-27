<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class LinkedProduct
 */
class LinkedProduct extends AbstractValueObject
{
    const TYPE_ACCESSORY = 'Accessory';
    const TYPE_REPLACEMENT = 'Replacement';
    const TYPE_SIMILAR = 'Similar';

    /**
     * @var string
     */
    private $type = '';

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        Assertion::string($type);

        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getPosition()
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
    public function getProductIdentifier()
    {
        return $this->productIdentifier;
    }

    /**
     * @param string $productIdentifier
     */
    public function setProductIdentifier($productIdentifier)
    {
        Assertion::uuid($productIdentifier);

        $this->productIdentifier = $productIdentifier;
    }
}
