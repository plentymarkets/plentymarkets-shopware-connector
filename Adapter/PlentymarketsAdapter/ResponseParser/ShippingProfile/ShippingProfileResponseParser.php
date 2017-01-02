<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class ShippingProfileResponseParser
 */
class ShippingProfileResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ShippingProfileResponseParser constructor.
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
        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            PlentymarketsAdapter::getName(),
            ShippingProfile::getType()
        );

        return ShippingProfile::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['backendName'],
        ]);
    }
}
