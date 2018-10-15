<?php

namespace ShopwareAdapter\ResponseParser\CustomerGroup;

use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;

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
