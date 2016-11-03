<?php

namespace PlentyConnector\Connector\Mapping;

use PlentyConnector\Connector\Identity\IdentityService;

/**
 * Class MappingService.
 */
class MappingService implements MappingServiceInterface
{
    /**
     * @var DefinitionInterface[]
     */
    private $definitions;

    /**
     * @var IdentityService
     */
    private $identityService;

    /**
     * MappingService constructor.
     *
     * @param IdentityService $identityService
     */
    public function __construct(IdentityService $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[] = $definition;
    }

    /**
     * @return array
     */
    public function getMappingInformation()
    {
        // TODO: parse definitions and return MappingInformation Struct -> used inside the backend
    }
}
