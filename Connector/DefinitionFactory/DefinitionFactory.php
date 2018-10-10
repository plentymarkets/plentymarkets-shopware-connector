<?php

namespace SystemConnector\DefinitionFactory;

use SystemConnector\ValidatorService\ValidatorServiceInterface;
use SystemConnector\ValueObject\Definition\Definition;

class DefinitionFactory implements DefinitionFactoryInterface
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    public function __construct(ValidatorServiceInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
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
