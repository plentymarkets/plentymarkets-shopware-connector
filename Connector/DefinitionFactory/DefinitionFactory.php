<?php

namespace SystemConnector\DefinitionFactory;

use SystemConnector\DefinitionProvider\Struct\Definition;
use SystemConnector\ValidatorService\ValidatorServiceInterface;

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
    public function factory($originAdapterName, $destinationAdapterName, $objectType, $priority = null): Definition
    {
        $definition = new Definition();
        $definition->setOriginAdapterName($originAdapterName);
        $definition->setDestinationAdapterName($destinationAdapterName);
        $definition->setObjectType($objectType);
        $definition->setPriority($priority);

        $this->validator->validate($definition);

        return $definition;
    }
}
