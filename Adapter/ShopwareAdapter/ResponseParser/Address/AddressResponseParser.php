<?php

namespace ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class AddressResponseParser
 */
class AddressResponseParser implements AddressResponseParserInterface
{
    use GetAttributeTrait;

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
        $entry['salutation'] = strtolower($entry['salutation']);

        $countryIdentitiy = $this->identityService->findOneBy([
            'adapterIdentifier' => $entry['country']['id'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Country::TYPE,
        ]);

        if (null === $countryIdentitiy) {
            throw new NotFoundException('country mapping missing - ' . json_encode($entry));
        }

        if ($entry['salutation'] === 'mr' || $entry['salutation'] === 'herr') {
            $gender = Customer::GENDER_MALE;
        } elseif ($entry['salutation'] === 'ms' || $entry['salutation'] === 'frau') {
            $gender = Customer::GENDER_FEMALE;
        } else {
            $gender = null;
        }

        $params = [
            'gender' => $gender,
            'firstname' => $entry['firstName'],
            'lastname' => $entry['lastName'],
            'street' => $entry['street'],
            'postalCode' => $entry['zipCode'],
            'city' => $entry['city'],
            'countryIdentifier' => $countryIdentitiy->getObjectIdentifier(),
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
