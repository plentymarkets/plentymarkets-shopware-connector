<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use Psr\Log\LoggerInterface;
use Shopware\Models\Customer\Group as GroupModel;
use Shopware\Models\Newsletter\Address;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class CustomerResponseParser
 */
class CustomerResponseParser implements CustomerResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CustomerResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param array $entry
     *
     * @return null|GroupModel
     */
    private function getCustomerGroup(array $entry)
    {
        /**
         * @var EntityRepository $customerGroupRepository
         */
        $customerGroupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var GroupModel $customerGroup
         */
        $customerGroup = $customerGroupRepository->findOneBy(['key' => $entry['groupKey']]);

        return $customerGroup;
    }

    /**
     * @param array $entry
     *
     * @return null|string
     */
    private function getLanguageIdentifier(array $entry)
    {
        /**
         * @var Repository $shopRepository
         */
        $shopRepository = $this->entityManager->getRepository(ShopModel::class);

        /**
         * @var ShopModel $shop
         */
        $customerShop = $shopRepository->find($entry['languageId']);

        if (null === $customerShop) {
            return null;
        }

        $languageIdentifier = $this->getIdentifier((string) $customerShop->getLocale()->getId(), Language::TYPE);

        if (null === $languageIdentifier) {
            return null;
        }

        return $languageIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $entry['salutation'] = strtolower($entry['salutation']);

        $shopIdentifier = $this->getIdentifier((string) $entry['shopId'], Shop::TYPE);

        $languageIdentifier = $this->getLanguageIdentifier($entry);
        if (null === $languageIdentifier) {
            $this->logger->warning('no customer language found');

            return null;
        }

        $customerGroup = $this->getCustomerGroup($entry);
        if (null === $customerGroup) {
            $this->logger->warning('no customer group found');

            return null;
        }

        $customerGroupIdentifier = $this->getIdentifier((string) $customerGroup->getId(), CustomerGroup::TYPE);

        if ($entry['salutation'] === 'mr') {
            $salutation = Customer::SALUTATION_MR;
        } elseif ($entry['salutation'] === 'ms') {
            $salutation = Customer::SALUTATION_MS;
        } else {
            $salutation = Customer::SALUTATION_FIRM;
        }

        if (empty($entry['birthday'])) {
            $birthday = null;
        } else {
            $birthday = DateTimeImmutable::createFromMutable($entry['birthday']);

            if (!($birthday instanceof DateTimeImmutable)) {
                $birthday = null;
            }
        }

        if (empty($entry['title'])) {
            $entry['title'] = null;
        }

        $customer = new Customer();
        $customer->setBirthday($birthday);
        $customer->setType($this->getCustomerTypeId($entry['accountMode']));
        $customer->setEmail($entry['email']);
        $customer->setFirstname($entry['firstname']);
        $customer->setLastname($entry['lastname']);
        $customer->setNumber($entry['number']);
        $customer->setSalutation($salutation);
        $customer->setTitle($entry['title']);
        $customer->setShopIdentifier($shopIdentifier);
        $customer->setLanguageIdentifier($languageIdentifier);
        $customer->setCustomerGroupIdentifier($customerGroupIdentifier);

        /**
         * @var EntityRepository $newsletterRepository
         */
        $newsletterRepository = $this->entityManager->getRepository(Address::class);
        $newsletter = $newsletterRepository->findOneBy(['email' => $entry['email']]);

        if ($newsletter !== null) {
            $customer->setNewsletter(true);

            if (null !== $newsletter->getAdded()) {
                $customer->setNewsletterAgreementDate(DateTimeImmutable::createFromMutable($newsletter->getAdded()));
            }
        }

        return $customer;
    }

    /**
     * @param int
     *
     * @return int
     */
    private function getCustomerTypeId($shopwareId)
    {
        switch ($shopwareId) {
            case \Shopware\Models\Customer\Customer::ACCOUNT_MODE_CUSTOMER:
                return Customer::TYPE_NORMAL;

            case \Shopware\Models\Customer\Customer::ACCOUNT_MODE_FAST_LOGIN:
                return Customer::TYPE_GUEST;
        }

        throw new \InvalidArgumentException('Unknown customer type ' . $shopwareId);
    }

    /**
     * @param int    $entry
     * @param string $type
     *
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }
}
