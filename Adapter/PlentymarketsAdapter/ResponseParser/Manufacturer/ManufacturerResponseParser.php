<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class ManufacturerResponseParser
 */
class ManufacturerResponseParser implements ManufacturerResponseParserInterface
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
        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            PlentymarketsAdapter::NAME,
            Manufacturer::TYPE
        );

        return Manufacturer::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
            'logoIdentifier' => !empty($entry['logoIdentifier']) ? $entry['logoIdentifier'] : null,
            'link' => !empty($entry['url']) ? $entry['url'] : null,
        ]);
    }
}
