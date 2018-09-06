<?php

namespace ShopwareAdapter\ResponseParser\ShippingProfile;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use ShopwareAdapter\ShopwareAdapter;

class ShippingProfileResponseParser implements ShippingProfileResponseParserInterface
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
            ShopwareAdapter::NAME,
            ShippingProfile::TYPE
        );

        return ShippingProfile::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
