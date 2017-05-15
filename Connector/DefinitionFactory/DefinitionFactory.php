<?php

namespace PlentyConnector\Connector\DefinitionFactory;

use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Class DefinitionFactory
 */
class DefinitionFactory
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * DefinitionFactory constructor.
     *
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(ValidatorServiceInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string   $originAdapterName
     * @param string   $destinationAdapterName
     * @param string   $objectType
     * @param null|int $priority
     *
     * @return ValueObjectInterface
     */
    public function factory($originAdapterName, $destinationAdapterName, $objectType, $priority = null)
    {
        $definition = Definition::fromArray([
            'originAdapterName' => $originAdapterName,
            'destinationAdapterName' => $destinationAdapterName,
            'objectType' => $objectType,
            'priority' => $priority,
        ]);

        $this->validator->validate($definition);

        return $definition;
    }
}
