<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class ShippingProfileResponseParser.
 */
class ShippingProfileResponseParser implements ShippingProfileResponseParserInterface
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
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            ShippingProfile::TYPE
        );

        return ShippingProfile::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name'       => $entry['backendName'],
        ]);
    }
}
