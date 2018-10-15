<?php

namespace ShopwareAdapter\ResponseParser\Language;

use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;

class LanguageResponseParser implements LanguageResponseParserInterface
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
            ShopwareAdapter::NAME,
            Language::TYPE
        );

        return Language::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'] . ' (' . $entry['locale'] . ')',
        ]);
    }
}
