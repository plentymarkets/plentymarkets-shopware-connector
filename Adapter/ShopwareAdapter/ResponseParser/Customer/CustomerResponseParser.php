<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Customer\Group as GroupModel;
use Shopware\Models\Newsletter\Address;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Shop\Shop;

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

        if ($entry['salutation'] === 'mr' || $entry['salutation'] === 'herr') {
            $gender = Customer::GENDER_MALE;
        } elseif ($entry['salutation'] === 'ms' || $entry['salutation'] === 'frau') {
            $gender = Customer::GENDER_FEMALE;
        } else {
            $gender = null;
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
        $customer->setGender($gender);
        $customer->setTitle($entry['title']);
        $customer->setShopIdentifier($shopIdentifier);
        $customer->setLanguageIdentifier($languageIdentifier);
        $customer->setCustomerGroupIdentifier($customerGroupIdentifier);

        /**
         * @var EntityRepository $newsletterRepository
         */
        $newsletterRepository = $this->entityManager->getRepository(Address::class);

        /**
         * @var null|Address $newsletter
         */
        $newsletter = $newsletterRepository->findOneBy(['email' => $entry['email']]);

        if ($newsletter !== null) {
            $customer->setNewsletter(true);

            if (null !== $newsletter->getAdded()) {
                $customer->setNewsletterAgreementDate($newsletter->getAdded()());
            }
        }

        return $customer;
    }

    /**
     * @param array $entry
     *
     * @return GroupModel
     */
    private function getCustomerGroup(array $entry): GroupModel
    {
        /**
         * @var EntityRepository $customerGroupRepository
         */
        $customerGroupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var GroupModel $customerGroup
         */
        return $customerGroupRepository->findOneBy(['key' => $entry['groupKey']]);
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
         * @var null|ShopModel $customerShop
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
     * @param $shopwareId
     *
     * @return string
     */
    private function getCustomerTypeId($shopwareId): string
    {
        switch ($shopwareId) {
            case CustomerModel::ACCOUNT_MODE_CUSTOMER:
                return Customer::TYPE_NORMAL;

            case CustomerModel::ACCOUNT_MODE_FAST_LOGIN:
                return Customer::TYPE_GUEST;
        }

        throw new InvalidArgumentException('Unknown customer type ' . $shopwareId);
    }

    /**
     * @param string $entry
     * @param string $type
     *
     * @return string
     */
    private function getIdentifier($entry, $type): string
    {
        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }
}
