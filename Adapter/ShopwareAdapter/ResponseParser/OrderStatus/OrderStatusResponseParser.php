<?php

namespace ShopwareAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderStatusResponseParser
 */
class OrderStatusResponseParser implements OrderStatusResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderStatusResponseParser constructor.
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
            ShopwareAdapter::NAME,
            OrderStatus::TYPE
        );

        if (!empty($entry['name'])) {
            $name = $entry['name'];
        } elseif (!empty($entry['description'])) {
            $name = $entry['description'];
        } else {
            $name = $entry['id'];
        }

        return OrderStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $name,
        ]);
    }
}
