<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class ManufacturerResponseParser
 */
class ManufacturerResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ManufacturerResponseParser constructor.
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
        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            PlentymarketsAdapter::getName(),
            Manufacturer::getType()
        );

        return Manufacturer::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
            'logo' => !empty($entry['logo']) ? $entry['name'] : null,
            'link' => !empty($entry['url']) ? $entry['name'] : null,
        ]);
    }
}
