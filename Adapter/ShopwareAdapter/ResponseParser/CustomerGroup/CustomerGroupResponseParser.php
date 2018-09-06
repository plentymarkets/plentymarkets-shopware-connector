<?php

namespace ShopwareAdapter\ResponseParser\CustomerGroup;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use ShopwareAdapter\ShopwareAdapter;

class CustomerGroupResponseParser implements CustomerGroupResponseParserInterface
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
            CustomerGroup::TYPE
        );

        return CustomerGroup::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
