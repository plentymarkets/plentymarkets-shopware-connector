<?php

namespace PlentymarketsAdapter\ResponseParser\Shop;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Shop\Shop;

class ShopResponseParser implements ShopResponseParserInterface
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
        if (null === $entry['storeIdentifier']) {
            return null;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['storeIdentifier'],
            PlentymarketsAdapter::NAME,
            Shop::TYPE
        );

        return Shop::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => empty($entry['name']) ? $entry['type'] : $entry['name'],
        ]);
    }
}
