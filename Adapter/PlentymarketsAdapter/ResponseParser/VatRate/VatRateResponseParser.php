<?php

namespace PlentymarketsAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class VatRateResponseParser
 */
class VatRateResponseParser implements VatRateResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * VatRateResponseParser constructor.
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
            PlentymarketsAdapter::NAME,
            VatRate::TYPE
        );

        return VatRate::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => !empty($entry['name']) ? $entry['name'] : $entry['vatRate'] . ' %',
        ]);
    }
}
