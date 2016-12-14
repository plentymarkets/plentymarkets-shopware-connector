<?php

namespace PlentymarketsAdapter\ResponseParser\Shop;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class ShopResponseParser
 */
class ShopResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ShopResponseParser constructor.
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
            (string)$entry['storeIdentifier'],
            PlentymarketsAdapter::getName(),
            Shop::getType()
        );

        $shop = Shop::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => empty($entry['name']) ? $entry['type'] : $entry['name']
        ]);

        return $shop;
    }
}
