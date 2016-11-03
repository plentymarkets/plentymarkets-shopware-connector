<?php

namespace PlentymarketsAdapter\ResponseParser;

use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * TODO: finalize.
 *
 * Class ResponseParser
 */
class ResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ResponseParser constructor.
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
    public function parseManufacturer($entry)
    {
        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            PlentymarketsAdapter::getName(),
            Manufacturer::getType()
        );

        $manufacturer = Manufacturer::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
            'logo' => !empty($entry['logo']) ? $entry['name'] : null,
            'link' => !empty($entry['url']) ? $entry['name'] : null,
        ]);

        return $manufacturer;
    }
}
