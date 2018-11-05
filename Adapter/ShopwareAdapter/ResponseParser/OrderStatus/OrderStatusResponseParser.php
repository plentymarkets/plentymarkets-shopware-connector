<?php

namespace ShopwareAdapter\ResponseParser\OrderStatus;

use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;

class OrderStatusResponseParser implements OrderStatusResponseParserInterface
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
            OrderStatus::TYPE
        );

        if (!empty($entry['name'])) {
            $name = $entry['name'];
        } else {
            $name = $entry['id'];
        }

        return OrderStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $name,
        ]);
    }
}
