<?php

namespace PlentyConnector\Connector\TransferObject\Product\Price;

use Assert\Assertion;

/**
 * Class Price.
 */
class Price implements PriceInterface
{
    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $pseudoPrice;

    /**
     * @var string|null
     */
    private $customerGroupIdentifier;

    /**
     * @var int
     */
    private $fromAmount;

    /**
     * @var int
     */
    private $toAmount;

    /**
     * Price constructor.
     *
     * @param float $price
     * @param float $pseudoPrice
     * @param string|null $customerGroupIdentifier
     * @param int $fromAmount
     * @param int $toAmount
     */
    public function __construct(
        $price,
        $pseudoPrice,
        $customerGroupIdentifier = null,
        $fromAmount,
        $toAmount = null
    ) {
        Assertion::float($price);
        Assertion::greaterOrEqualThan($price, '0.0');
        Assertion::float($pseudoPrice);
        Assertion::nullOrUuid($customerGroupIdentifier);
        Assertion::integer($fromAmount);
        Assertion::greaterOrEqualThan($fromAmount, 1);
        Assertion::integer($toAmount);

        if ($pseudoPrice <= $price) {
            $pseudoPrice = 0.0;
        }

        $this->price = $price;
        $this->pseudoPrice = $pseudoPrice;
        $this->customerGroupIdentifier = $customerGroupIdentifier;
        $this->fromAmount = $fromAmount;
        $this->toAmount = $toAmount;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'price',
            'pseudoPrice',
            'customerGroupIdentifier',
            'from',
            'to',
        ]);

        return new self(
            $params['price'],
            $params['pseudoPrice'],
            $params['customerGroupIdentifier'],
            $params['from'],
            $params['to']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * {@inheritdoc}
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupIdentifier()
    {
        return $this->customerGroupIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromAmount()
    {
        return $this->fromAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function getToAmount()
    {
        return $this->toAmount;
    }
}
