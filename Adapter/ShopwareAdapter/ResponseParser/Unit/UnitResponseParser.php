<?php

namespace ShopwareAdapter\ResponseParser\Unit;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class UnitResponseParser
 */
class UnitResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * UnitResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            ShopwareAdapter::getName(),
            Unit::getType()
        );

        $unit = Unit::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
            'unit' => $entry['unitOfMeasurement']
        ]);

        return $unit;
    }
}
