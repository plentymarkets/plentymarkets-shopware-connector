<?php

namespace PlentymarketsAdapter\Helper;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;

/**
 * Class VariationHelper
 */
class VariationHelper implements VariationHelperInterface
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
     * VariationHelper constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface          $logger
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

            $isMappedIdentity = $this->identityService->isMappedIdentity(
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

    /**
     * @return array
     */
    public function getMappedPlentyClientIds()
    {
        $identities = $this->identityService->findBy([
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (!isset($identities)) {
            $this->logger->notice('no plentyIds found');

            return [];
        }

        $clientIds = [];

        foreach ($identities as $identity) {
            $isMappedIdentity = $this->identityService->isMappedIdentity(
                $identity->getObjectIdentifier(),
                $identity->getObjectType(),
                $identity->getAdapterName()
            );

            if (!$isMappedIdentity) {
                continue;
            }

            $clientIds[] = $identity->getAdapterIdentifier();
        }

        return $clientIds;
    }
}
