<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\MediaCategory\MediaCategory;

class MediaCategoryResponseParser implements MediaCategoryResponseParserInterface
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
            MediaCategory::TYPE
        );

        return MediaCategory::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => (string) $entry['name'],
        ]);
    }
}
