<?php

namespace ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
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
        $countryIdentitiy = $this->identityService->findOneBy([
            'adapterIdentifier' => $entry['country']['id'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Country::TYPE,
        ]);

        if (null === $countryIdentitiy) {
            // TODO: throw
        }

        return Address::fromArray([
            'company' => $entry['company'],
            'department' => $entry['department'],
            'salutation' => $entry['salutation'],
            'title' => $entry['title'],
            'firstname' => $entry['firstName'],
            'lastname' => $entry['lastName'],
            'street' => $entry['street'],
            'zipcode' => $entry['zipCode'],
            'city' => $entry['city'],
            'countryIdentifier' => $countryIdentitiy->getObjectIdentifier(),
            'vatId' => isset($entry['vatId']) ? $entry['vatId'] : null,
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);
    }
}
