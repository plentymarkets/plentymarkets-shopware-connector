<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class PaymentStatusResponseParser
 */
class PaymentStatusResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * PaymentStatusResponseParser constructor.
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
            PlentymarketsAdapter::getName(),
            PaymentStatus::getType()
        );

        return PaymentStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name']
        ]);
    }
}
