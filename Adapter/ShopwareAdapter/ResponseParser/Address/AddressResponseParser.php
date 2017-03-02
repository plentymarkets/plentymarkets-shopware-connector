<?php

namespace ShopwareAdapter\ResponseParser\Address;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
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
        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            ShopwareAdapter::NAME,
            Address::TYPE
        );

        //TODO: state, additional address lines
        return Address::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'company' => $entry['company'],
            'department' => $entry['department'],
            'salutation' => $entry['salutation'],
            'title' => $entry['title'],
            'firstname' => $entry['firstName'],
            'lastname' => $entry['lastName'],
            'street' => $entry['street'],
            'zipcode' => $entry['zipCode'],
            'city' => $entry['city'],
            'countryIdentifier' => $entry['country']['iso3'],
            'vatId' => isset($entry['vatId']) ? $entry['vatId'] : null,
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);
    }
}
