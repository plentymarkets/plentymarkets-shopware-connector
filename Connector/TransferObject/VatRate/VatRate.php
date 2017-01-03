<?php

namespace PlentyConnector\Connector\TransferObject\VatRate;

use Assert\Assertion;

/**
 * Class VatRate
 */
class VatRate implements VatRateInterface
{
    const TYPE = 'VatRate';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $vatRate;

    /**
     * VatRate constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param float $vatRate
     */
    public function __construct($identifier, $name, $vatRate)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::float($vatRate);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->vatRate = $vatRate;
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
            'vatRate'
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['vatRate']
        );
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }
}
