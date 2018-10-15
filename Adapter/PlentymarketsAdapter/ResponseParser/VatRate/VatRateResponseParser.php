<?php

namespace PlentymarketsAdapter\ResponseParser\VatRate;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\VatRate\VatRate;

class VatRateResponseParser implements VatRateResponseParserInterface
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
            PlentymarketsAdapter::NAME,
            VatRate::TYPE
        );

        return VatRate::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => !empty($entry['name']) ? $entry['name'] : $entry['vatRate'] . ' %',
        ]);
    }
}
