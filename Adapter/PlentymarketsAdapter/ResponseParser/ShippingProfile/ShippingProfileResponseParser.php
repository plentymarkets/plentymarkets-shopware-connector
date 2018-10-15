<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;

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
            PlentymarketsAdapter::NAME,
            ShippingProfile::TYPE
        );

        return ShippingProfile::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['backendName'],
        ]);
    }
}
