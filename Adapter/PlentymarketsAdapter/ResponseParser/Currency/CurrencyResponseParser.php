<?php

namespace PlentymarketsAdapter\ResponseParser\Currency;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class CurrencyResponseParser
 */
class CurrencyResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * CurrencyResponseParser constructor.
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
            (string)$entry['currency'],
            PlentymarketsAdapter::NAME,
            Currency::TYPE
        );

        return Currency::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['currency'],
        ]);
    }
}
