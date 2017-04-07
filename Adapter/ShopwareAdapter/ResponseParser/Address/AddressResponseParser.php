<?php

namespace ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
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
            throw new NotFoundException('country mapping missing - ' . json_encode($entry['country']));
        }

        if ($entry['salutation'] === 'mr') {
            $salutation = Customer::SALUTATION_MR;
        } elseif ($entry['salutation'] === 'ms') {
            $salutation = Customer::SALUTATION_MS;
        } else {
            $salutation = Customer::SALUTATION_FIRM;
        }

        return Address::fromArray([
            'company' => $entry['company'],
            'department' => $entry['department'],
            'salutation' => $salutation,
            'title' => $entry['title'],
            'firstname' => $entry['firstName'],
            'lastname' => $entry['lastName'],
            'street' => $entry['street'],
            'additional' => $entry['additionalAddressLine1'],
            'postalCode' => $entry['zipCode'],
            'city' => $entry['city'],
            'countryIdentifier' => $countryIdentitiy->getObjectIdentifier(),
            'vatId' => isset($entry['vatId']) ? $entry['vatId'] : null,
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);
    }
}
