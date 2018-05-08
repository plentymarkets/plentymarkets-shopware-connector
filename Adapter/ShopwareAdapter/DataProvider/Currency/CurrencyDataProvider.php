<?php

namespace ShopwareAdapter\DataProvider\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
     * {@inheritdoc}
     */
    public function getCurrencyIdentifierByCode($code)
    {
        /**
         * @var EntityRepository $currencyRepository
         */
        $currencyRepository = $this->entityManager->getRepository(Currency::class);

        /**
         * @var Currency|null $currencyModel
         */
        $currencyModel = $currencyRepository->findOneBy(['currency' => $code]);

        if (null === $currencyModel) {
            throw new InvalidArgumentException('invalid currency code');
        }

        return $currencyModel->getId();
    }
}
