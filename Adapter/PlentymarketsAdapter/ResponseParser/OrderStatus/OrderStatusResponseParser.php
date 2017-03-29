<?php

namespace PlentymarketsAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentymarketsAdapter\PlentymarketsAdapter;

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
        if (empty($entry['id'])) {
            return null;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            OrderStatus::TYPE
        );

        return OrderStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $entry['id'],
        ]);
    }
}
