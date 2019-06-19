<?php

namespace ShopwareAdapter\ResponseParser\Address;

use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Customer\Customer;

class AddressResponseParser implements AddressResponseParserInterface
{
    use GetAttributeTrait;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * @param array $entry
     *
     * @throws NotFoundException
     * @throws NotFoundException
     *
     * @return null|Address
     */
    public function parse(array $entry)
    {
        $entry['salutation'] = strtolower($entry['salutation']);

        $countryIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => $entry['country']['id'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Country::TYPE,
        ]);

        if (null === $countryIdentity) {
            throw new NotFoundException('country mapping missing - ' . json_encode($entry));
        }

        if ($entry['salutation'] === 'mr' || $entry['salutation'] === 'herr') {
            $gender = Customer::GENDER_MALE;
        } elseif ($entry['salutation'] === 'ms' || $entry['salutation'] === 'frau') {
            $gender = Customer::GENDER_FEMALE;
        } else {
            $gender = Customer::GENDER_DIVERSE;
        }

        $params = [
            'gender' => $gender,
            'firstname' => $entry['firstName'],
            'lastname' => $entry['lastName'],
            'street' => $entry['street'],
            'postalCode' => $entry['zipCode'],
            'city' => $entry['city'],
            'countryIdentifier' => $countryIdentity->getObjectIdentifier(),
            'vatId' => !empty($entry['vatId']) ? $entry['vatId'] : null,
        ];

        if (!empty($entry['attribute'])) {
            $params['attributes'] = $this->getAttributes($entry['attribute']);
        }

        if (!empty($entry['department'])) {
            $params['department'] = $entry['department'];
        }

        if (!empty($entry['title'])) {
            $params['title'] = $entry['title'];
        }

        if (!empty($entry['company'])) {
            $params['company'] = $entry['company'];
        }

        if (!empty(trim($entry['additionalAddressLine1']))) {
            $params['additional'] = $entry['additionalAddressLine1'];
        }

        if (isset($entry['phone']) && !empty(trim($entry['phone']))) {
            $params['phoneNumber'] = $entry['phone'];
        }

        return Address::fromArray($params);
    }
}
