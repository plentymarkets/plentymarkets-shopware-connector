<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentMethod;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class PaymentMethodResponseParser
 */
class PaymentMethodResponseParser implements PaymentMethodResponseParserInterface
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
            PlentymarketsAdapter::NAME,
            PaymentMethod::TYPE
        );

        return PaymentMethod::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
