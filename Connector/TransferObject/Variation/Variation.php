<?php

namespace PlentyConnector\Connector\TransferObject\Variation;

use Assert\Assertion;

/**
 * Class Variation
 */
class Variation implements VariationInterface
{
    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifer;

    /**
     * @var string
     */
    private $name;

    /**
     * Variation constructor.
     *
     * @param string $identifer
     * @param string $name
     */
    public function __construct($identifer, $name)
    {
        Assertion::uuid($identifer);
        Assertion::string($name);

        $this->identifer = $identifer;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return 'Variation';
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifer'],
            $params['name']
        );
    }
}
