<?php

namespace ShopwareAdapter\DataProvider\Tax;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopware\Models\Tax\Rule;
use Shopware\Models\Tax\Tax;

class TaxDataProvider implements TaxDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $taxRepository;

    /**
     * @var EntityRepository
     */
    private $taxRulesRepository;

    /**
     * TaxDataProvider constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->taxRepository = $entityManager->getRepository(Tax::class);
        $this->taxRulesRepository = $entityManager->getRepository(Rule::class);
    }

    /**
     * @param int $countryId
     *
     * @return Tax $taxModel|null
     */
    public function getTax(float $rate, int $countryId = null)
    {
        if (null !== $countryId) {
            /**
             * @var Rule $taxRule
             */
            $taxRule = $this->taxRulesRepository->findOneBy([
                'tax' => $rate,
                'countryId' => $countryId,
            ]);

            if (null !== $taxRule) {
                return $taxRule->getGroup();
            }
        }

        return $this->taxRepository->findOneBy([
                'tax' => (float) $rate,
        ]);
    }
}
