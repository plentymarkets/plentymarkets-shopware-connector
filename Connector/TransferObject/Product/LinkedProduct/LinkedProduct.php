<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class LinkedProduct
 */
class LinkedProduct extends AbstractValueObject
{
    /**
     * @var string
     */
    private $type = '';

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
