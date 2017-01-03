<?php

namespace PlentyConnector\Connector\TransferObject\Country;

use Assert\Assertion;

/**
 * Class Country
 */
class Country implements CountryInterface
{
    const TYPE = 'Country';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * ISO 3166-1 alpha-2
     *
     * @var string
     */
    private $countryCode;

    /**
     * Country constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $countryCode
     */
    public function __construct($identifier, $name, $countryCode)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::string($countryCode);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->countryCode = $countryCode;
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
            'countryCode'
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['countryCode']
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
    public function getCountryCode()
    {
        return $this->countryCode;
    }
}
