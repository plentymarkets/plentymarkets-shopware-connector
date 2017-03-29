<?php

namespace ShopwareAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class VatRateResponseParser.
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
            ShopwareAdapter::NAME,
            VatRate::TYPE
        );

        return VatRate::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name'       => $entry['name'],
        ]);
    }
}
