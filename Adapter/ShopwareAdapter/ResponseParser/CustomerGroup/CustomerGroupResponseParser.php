<?php

namespace ShopwareAdapter\ResponseParser\CustomerGroup;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class CustomerGroupResponseParser
 */
class CustomerGroupResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * CustomerGroupResponseParser constructor.
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
            CustomerGroup::getType()
        );

        return CustomerGroup::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
