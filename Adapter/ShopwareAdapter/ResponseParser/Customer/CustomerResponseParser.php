<?php

namespace ShopwareAdapter\ResponseParser\Customer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
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
     * CountryResponseParser constructor.
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
    public function parse(array $entry)
    {
        $shopIdentity = $this->getIdentifier((string) $entry['shopId'], Shop::TYPE);
        $languageIdentity = $this->getIdentifier((string) $entry['languageId'], Language::TYPE);

        return Customer::fromArray([
            'birthday' => $entry['birthday'] ? \DateTimeImmutable::createFromFormat('Y-m-d', $entry['birthday']) : null,
            'customerType' => $this->getCustomerTypeId($entry['accountMode']),
            'email' => $entry['email'],
            'firstname' => $entry['firstname'],
            'lastname' => $entry['lastname'],
            'number' => $entry['number'],
            'salutation' => $entry['salutation'],
            'title' => $entry['title'],
            'newsletter' => (bool) $entry['newsletter'],
            'shopIdentifier' => $shopIdentity,
            'languageIdentifier' => $languageIdentity,
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
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }
}
