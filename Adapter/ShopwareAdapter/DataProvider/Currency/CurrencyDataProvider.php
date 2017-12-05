<?php

namespace ShopwareAdapter\DataProvider\Currency;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
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
         * @var ObjectRepository $currencyRepository
         */
        $currencyRepository = $this->entityManager->getRepository(Currency::class);

        $currencyModel = $currencyRepository->findOneBy(['currency' => $code]);

        if (null === $currencyModel) {
            throw new InvalidArgumentException('invalid currency code');
        }

        return $currencyModel->getId();
    }
}
