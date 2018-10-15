<?php

namespace PlentymarketsAdapter\ResponseParser\Currency;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Currency\Currency;

class CurrencyResponseParser implements CurrencyResponseParserInterface
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
            (string) $entry['currency'],
            PlentymarketsAdapter::NAME,
            Currency::TYPE
        );

        return Currency::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['currency'],
        ]);
    }
}
