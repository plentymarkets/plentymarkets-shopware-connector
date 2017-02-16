<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use Assert\Assertion;

/**
 * Class LinkedProduct
 */
class LinkedProduct implements LinkedProductInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $productIdentifier;

    /**
     * LinkedProduct constructor.
     *
     * @param string $type
     * @param array $productIdentifier
     */
    public function __construct($type, $productIdentifier)
    {
        Assertion::string($type);
        Assertion::uuid($productIdentifier);

        $this->type = $type;
        $this->productIdentifier = $productIdentifier;
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'type',
            'productIdentifier',
        ]);

        return new self(
            $params['type'],
            $params['productIdentifier']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdentifier()
    {
        return $this->productIdentifier;
    }
}
