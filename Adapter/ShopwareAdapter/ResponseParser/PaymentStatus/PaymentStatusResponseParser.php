<?php

namespace ShopwareAdapter\ResponseParser\PaymentStatus;

use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;

class PaymentStatusResponseParser implements PaymentStatusResponseParserInterface
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
            PaymentStatus::TYPE
        );

        if (!empty($entry['name'])) {
            $name = $entry['name'];
        } else {
            $name = $entry['id'];
        }

        return PaymentStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $name,
        ]);
    }
}
