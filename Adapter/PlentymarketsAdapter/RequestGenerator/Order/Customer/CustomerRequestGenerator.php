<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Customer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class CustomerRequestGenerator
 */
class CustomerRequestGenerator implements CustomerRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * CustomerRequestGenerator constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Customer $customer, Order $order)
    {
        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShopIdentifier(),
            'objectType' => Shop::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $shopIdentity) {
            throw new NotFoundException('shop not mapped - ' . $order->getShopIdentifier());
        }

        $languageIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $customer->getLanguageIdentifier(),
            'objectType' => Language::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $languageIdentity) {
            throw new NotFoundException('language not found - ' . $customer->getLanguageIdentifier());
        }

        $customerGroupIdentitiy = $this->identityService->findOneBy([
            'objectIdentifier' => $customer->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $customerParams = [
            'number' => $customer->getNumber(),
            'typeId' => 1,
            'firstName' => $customer->getFirstname(),
            'lastName' => $customer->getLastname(),
            'gender' => Customer::SALUTATION_MR === $customer->getSalutation() ? 'male' : 'female',
            'lang' => $languageIdentity->getAdapterIdentifier(),
            'singleAccess' => Customer::TYPE_GUEST === $customer->getType(),
            'plentyId' => $shopIdentity->getAdapterIdentifier(),
            'newsletterAllowanceAt' => '',
            'lastOrderAt' => $order->getOrderTime()->format(DATE_W3C),
            'options' => [],
            'referrerId' => 1,
        ];

        if (null !== $customerGroupIdentitiy) {
            $customerParams['classId'] = (int) $customerGroupIdentitiy->getAdapterIdentifier();
        }

        if (null !== $customer->getBirthday()) {
            $customerParams['birthdayAt'] = $customer->getBirthday()->format(DATE_W3C);
        }

        if ($customer->getNewsletter()) {
            if (null !== $customer->getNewsletterAgreementDate()) {
                $customerParams['newsletterAllowanceAt'] = $customer->getNewsletterAgreementDate()->format(DATE_W3C);
            } else {
                $customerParams['newsletterAllowanceAt'] = $order->getOrderTime()->format(DATE_W3C);
            }
        }

        if (null !== $customer->getPhoneNumber()) {
            $customerParams['options'][] = [
                'typeId' => 1,
                'subTypeId' => 4,
                'value' => $customer->getPhoneNumber(),
                'priority' => 0,
            ];
        }

        if (null !== $customer->getMobilePhoneNumber()) {
            $customerParams['options'][] = [
                'typeId' => 1,
                'subTypeId' => 2,
                'value' => $customer->getMobilePhoneNumber(),
                'priority' => 0,
            ];
        }

        if (!empty($customer->getEmail())) {
            $customerParams['options'][] = [
                'typeId' => 2,
                'subTypeId' => 4,
                'value' => $customer->getEmail(),
                'priority' => 0,
            ];
        }

        return $customerParams;
    }
}
