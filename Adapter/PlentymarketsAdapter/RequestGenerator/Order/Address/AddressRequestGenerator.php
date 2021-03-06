<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\Address;

use Exception;
use PlentymarketsAdapter\PlentymarketsAdapter;
use RuntimeException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Order;
use VIISON\AddressSplitter\AddressSplitter;

class AddressRequestGenerator implements AddressRequestGeneratorInterface
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
     * @param Address $address
     * @param Order   $order
     * @param int     $addressType
     *
     * @return array
     */
    public function generate(Address $address, Order $order, $addressType = 0): array
    {
        $countryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $address->getCountryIdentifier(),
            'objectType' => Country::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $countryIdentity) {
            throw new RuntimeException('country not mapped');
        }

        try {
            $splitResult = AddressSplitter::splitAddress($address->getStreet());

            $address1 = $splitResult['streetName'];
            $address2 = $splitResult['houseNumber'];
            $address3 = trim($splitResult['additionToAddress1'] . ' ' . $splitResult['additionToAddress2']);
        } catch (Exception $exception) {
            $address1 = $address->getStreet();
            $address2 = '';
            $address3 = '';
        }

        $params = [
            'name1' => trim($address->getCompany() . ' ' . $address->getDepartment()),
            'name2' => $address->getFirstname(),
            'name3' => $address->getLastname(),
            'gender' => $address->getGender(),
            'postalCode' => $address->getPostalCode(),
            'town' => $address->getCity(),
            'countryId' => $countryIdentity->getAdapterIdentifier(),
            'typeId' => $addressType,
        ];

        if (0 === strcasecmp($address1, 'Packstation')) {
            $params = array_merge($params, [
                'isPackstation' => true,
                'address1' => 'PACKSTATION',
                'address2' => $address2,
                'options' => [
                    [
                        'typeId' => 5,
                        'value' => $order->getCustomer()->getEmail(),
                    ],
                ],
            ]);

            if (null !== $address->getAdditional()) {
                $params['options'][] = [
                    'typeId' => 6,
                    'value' => $address->getAdditional(),
                ];
            }
        } elseif (0 === strcasecmp($address1, 'Postfiliale')) {
            $params = array_merge($params, [
                'isPostfiliale' => true,
                'address1' => 'POSTFILIALE',
                'address2' => $address2,
                'options' => [
                    [
                        'typeId' => 5,
                        'value' => $order->getCustomer()->getEmail(),
                    ],
                ],
            ]);

            if (null !== $address->getAdditional()) {
                $params['options'][] = [
                    'typeId' => 6,
                    'value' => $address->getAdditional(),
                ];
            }
        } else {
            $params = array_merge($params, [
                'address1' => $address1,
                'address2' => $address2,
                'address3' => $address->getAdditional(),
                'address4' => $address3,
                'options' => [
                    [
                        'typeId' => 5,
                        'value' => $order->getCustomer()->getEmail(),
                    ],
                ],
            ]);
        }

        if (null !== $order->getCustomer()->getPhoneNumber()) {
            $params['options'][] = [
                'typeId' => 4,
                'value' => $order->getCustomer()->getPhoneNumber(),
            ];
        }

        if (null !== $address->getVatId()) {
            $params['options'][] = [
                'typeId' => 1,
                'value' => $address->getVatId(),
            ];
        }

        return $params;
    }
}
