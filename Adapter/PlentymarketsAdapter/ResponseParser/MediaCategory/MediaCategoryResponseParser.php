<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\PlentymarketsAdapter;

class MediaCategoryResponseParser implements MediaCategoryResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * MediaCategoryResponseParser constructor.
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
            MediaCategory::TYPE
        );

        return MediaCategory::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $entry['name'],
        ]);
    }
}
