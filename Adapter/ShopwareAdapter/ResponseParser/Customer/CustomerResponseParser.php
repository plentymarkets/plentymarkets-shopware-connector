<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use Shopware\Models\Customer\Group as GroupModel;
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
     * CountryResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $entry['salutation'] = strtolower($entry['salutation']);

        $shopIdentifier = $this->getIdentifier((string) $entry['shopId'], Shop::TYPE);
        $languageIdentifier = $this->getIdentifier((string) $entry['languageId'], Language::TYPE);

        /**
         * @var EntityRepository $customerGroupRepository
         */
        $customerGroupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var GroupModel $customerGroup
         */
        $customerGroup = $customerGroupRepository->findOneBy(['key' => $entry['groupKey']]);

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
            $timezone = new DateTimeZone('UTC');
            $birthday = DateTimeImmutable::createFromFormat('Y-m-d', $entry['birthday'], $timezone);

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
        $customer->setNewsletter((bool) $entry['newsletter']);
        $customer->setShopIdentifier($shopIdentifier);
        $customer->setLanguageIdentifier($languageIdentifier);
        $customer->setCustomerGroupIdentifier($customerGroupIdentifier);

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
