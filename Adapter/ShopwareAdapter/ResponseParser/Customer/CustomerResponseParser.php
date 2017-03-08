<?php

namespace ShopwareAdapter\ResponseParser\Customer;

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
     * @param EntityManagerInterface $entityManager
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

        $shopIdentifier = $this->getIdentifier((string)$entry['shopId'], Shop::TYPE);
        $languageIdentifier = $this->getIdentifier((string)$entry['languageId'], Language::TYPE);

        /**
         * @var EntityRepository $customerGroupRepository
         */
        $customerGroupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var GroupModel $customerGroup
         */
        $customerGroup = $customerGroupRepository->findOneBy(['key' => $entry['groupKey']]);

        $customerGroupIdentifier = $this->getIdentifier((string)$customerGroup->getId(), CustomerGroup::TYPE);

        if ($entry['salutation'] === 'mr') {
            $salutation = Customer::SALUTATION_MR;
        } elseif ($entry['salutation'] === 'ms') {
            $salutation = Customer::SALUTATION_MS;
        } else {
            $salutation = Customer::SALUTATION_FIRM;
        }

        return Customer::fromArray([
            'birthday' => $entry['birthday'] ? \DateTimeImmutable::createFromFormat('Y-m-d', $entry['birthday']) : null,
            'customerType' => $this->getCustomerTypeId($entry['accountMode']),
            'email' => $entry['email'],
            'firstname' => $entry['firstname'],
            'lastname' => $entry['lastname'],
            'number' => $entry['number'],
            'salutation' => $salutation,
            'title' => $entry['title'],
            'newsletter' => (bool)$entry['newsletter'],
            'shopIdentifier' => $shopIdentifier,
            'languageIdentifier' => $languageIdentifier,
            'customerGroupIdentifier' => $customerGroupIdentifier
        ]);
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
     * @param int $entry
     * @param string $type
     *
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        return $this->identityService->findOneOrThrow(
            (string)$entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }
}
