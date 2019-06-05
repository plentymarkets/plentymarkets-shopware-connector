<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Customer;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Shop\Shop;

class CustomerRequestGenerator implements CustomerRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * @param Customer $customer
     * @param Order    $order
     *
     * @throws NotFoundException
     *
     * @return array
     */
    public function generate(Customer $customer, Order $order): array
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

        $customerGroupIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $customer->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $customerParams = [
            'number' => $customer->getNumber(),
            'typeId' => 1,
            'firstName' => $customer->getFirstname(),
            'lastName' => $customer->getLastname(),
            'gender' => $customer->getGender() === Customer::GENDER_MALE ? 'male' : 'female',
            'lang' => $languageIdentity->getAdapterIdentifier(),
            'singleAccess' => $customer->getType() === Customer::TYPE_GUEST,
            'plentyId' => $shopIdentity->getAdapterIdentifier(),
            'newsletterAllowanceAt' => '',
            'lastOrderAt' => $order->getOrderTime()->format(DATE_W3C),
            'options' => [],
            'referrerId' => 1,
        ];

        if (null !== $customerGroupIdentity) {
            $customerParams['classId'] = (int) $customerGroupIdentity->getAdapterIdentifier();
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
