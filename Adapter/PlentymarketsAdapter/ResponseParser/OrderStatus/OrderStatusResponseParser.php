<?php

namespace PlentymarketsAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class OrderStatusResponseParser
 */
class OrderStatusResponseParser implements ResponseParserInterface
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
     * TODO: change name to something meaningfull
     *
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            PlentymarketsAdapter::getName(),
            OrderStatus::getType()
        );

        return OrderStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string)$entry['id']
        ]);
    }
}
