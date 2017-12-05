<?php

namespace ShopwareAdapter\ResponseParser\Country;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class CountryResponseParser
 */
class CountryResponseParser implements CountryResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * CountryResponseParser constructor.
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
            Country::TYPE
        );

        return Country::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name'       => $entry['name'],
        ]);
    }
}
