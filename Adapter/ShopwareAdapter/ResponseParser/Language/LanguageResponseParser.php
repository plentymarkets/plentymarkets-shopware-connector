<?php

namespace ShopwareAdapter\ResponseParser\Language;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class LanguageResponseParser
 */
class LanguageResponseParser implements LanguageResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * LanguageResponseParser constructor.
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
            ShopwareAdapter::NAME,
            Language::TYPE
        );

        return Language::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'] . ' (' . $entry['locale'] . ')',
        ]);
    }
}
