<?php

namespace PlentymarketsAdapter\Helper;

use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use Psr\Log\LoggerInterface;
/**
 * Class ShopIdentifierHelper
 */
class ShopIdentifierHelper implements ShopIdentifierHelperInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ShopIdentifierHelper constructor.
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(IdentityServiceInterface $identityService, LoggerInterface $logger)
    {
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * @param array $variation
     *
     * @return array
     */
    public function getShopIdentifiers(array $variation)
    {
        $identifiers = [];

        foreach ($variation['variationClients'] as $client) {
            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => $client['plentyId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            if (null === $identity) {
                $this->logger->notice('shop not found', $client);

                continue;
            }

            $isMappedIdentity = $this->identityService->isMapppedIdentity(
                $identity->getObjectIdentifier(),
                $identity->getObjectType(),
                $identity->getAdapterName()
            );

            if (!$isMappedIdentity) {
                continue;
            }

            $identifiers[] = $identity->getObjectIdentifier();
        }

        return $identifiers;
    }
}
