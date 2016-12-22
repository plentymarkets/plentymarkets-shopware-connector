<?php

namespace PlentyConnector\Connector\TransferObject\Currency;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Currency
 */
class Currency implements CurrencyInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * ISO 4217 based currency name
     *
     * @var string
     */
    private $currency;

    /**
     * Currency constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $currency
     */
    public function __construct($identifier, $name, $currency)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::string($currency);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->currency = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::CURRENCY;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'currency'
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['currency']
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
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
