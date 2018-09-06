<?php

namespace PlentymarketsAdapter\ResponseParser\Unit;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentymarketsAdapter\PlentymarketsAdapter;

class UnitResponseParser implements UnitResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Unit::TYPE
        );

        // use first unit name as name
        $name = implode(' / ', array_column($entry['names'], 'name'));

        return Unit::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $name,
        ]);
    }
}
