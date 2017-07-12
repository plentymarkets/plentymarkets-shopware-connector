<?php

namespace ShopwareAdapter\DataProvider\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Currency;

/**
 * Class CurrencyDataProvider
 */
class CurrencyDataProvider implements CurrencyDataProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CurrencyDataProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $code
     *
     * @return int
     */
    public function getCurrencyIdentifierByCode($code)
    {
        /**
         * @var ModelRepository $currencyRepository
         */
        $currencyRepository = $this->entityManager->getRepository(Currency::class);

        $currencyModel = $currencyRepository->findOneBy(['currency' => $code]);

        if (null === $currencyModel) {
            throw new InvalidArgumentException('invalid currency code');
        }

        return $currencyModel->getId();
    }
}
