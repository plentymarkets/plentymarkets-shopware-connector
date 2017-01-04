<?php

namespace PlentymarketsAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class VatRateResponseParser
 */
class VatRateResponseParser implements ResponseParserInterface
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
            (string)$entry['id'],
            PlentymarketsAdapter::getName(),
            VatRate::getType()
        );

        return VatRate::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => null !== $entry['name'] ? $entry['name'] : $entry['vatRate'] . ' %',
        ]);
    }
}
