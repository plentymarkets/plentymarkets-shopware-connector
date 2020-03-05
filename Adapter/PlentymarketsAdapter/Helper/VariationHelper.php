<?php

namespace PlentymarketsAdapter\Helper;

use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Shop\Shop;

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
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        ConfigServiceInterface $configService
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->configService = $configService;
    }

    public function getShopIdentifiers(array $variation): array
    {
        $identifiers = [];

        foreach ((array) $variation['variationClients'] as $client) {
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

    public function getMappedPlentyClientIds(): array
    {
        $identities = $this->identityService->findBy([
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (empty($identities)) {
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

    public function getMainVariation(array $variations): array
    {
        $mainVariation = array_filter($variations, static function ($variation) {
            return $variation['isMain'] === true;
        });

        if (empty($mainVariation)) {
            return [];
        }

        return reset($mainVariation);
    }

    public function getMainVariationNumber(array $mainVariation, array $variations = []): string
    {
        $found = false;

        $mainVariationNumber = (string) $mainVariation['id'];

        if ($this->configService->get('variation_number_field', 'number') === 'number') {
            $mainVariationNumber = (string) $mainVariation['number'];
        }

        foreach ($variations as $variation) {
            if ($variation->getNumber() === $mainVariationNumber) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $checkActiveMainVariation = json_decode($this->configService->get('check_active_main_variation'), 512);

            if (!$checkActiveMainVariation && !$mainVariation['isActive']) {
                foreach ($variations as $variation) {
                    if ($variation->getActive()) {
                        return $variation->getNumber();
                    }
                }
            }

            return $mainVariationNumber;
        }

        foreach ($variations as $variation) {
            if ($variation->getActive()) {
                return $variation->getNumber();
            }
        }

        return $mainVariationNumber;
    }
}
