<?php

namespace ShopwareAdapter\ResponseParser\PaymentMethod;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class PaymentMethodResponseParser
 */
class PaymentMethodResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * PaymentMethodResponseParser constructor.
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
            (string)$entry['id'],
            ShopwareAdapter::NAME,
            PaymentMethod::TYPE
        );

        return PaymentMethod::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
