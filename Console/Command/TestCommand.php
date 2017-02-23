<?php

namespace PlentyConnector\Console\Command;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\MappingService\MappingServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\Client\Client;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\Logger;
use Shopware\Components\Routing\Context;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VIISON\AddressSplitter\AddressSplitter;

/**
 * Class TestCommand
 */
class TestCommand extends ShopwareCommand
{
    /**
     * @var ClientInterface $client
     */
    private $client;

    /**
     * @var IdentityServiceInterface $identityService
     */
    private $identityService;

    /**
     * TestCommand constructor.
     */
    public function __construct()
    {
        $this->client = Shopware()->Container()->get('plentmarkets_adapter.client');
        $this->identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:test');
        $this->setDescription('test');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return null|int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Logger
         */
        $logger = $this->container->get('plenty_connector.logger');
        $logger->pushHandler(new ConsoleHandler($output));

        /**
        // Context erstellen und initialisieren
        $repository = Shopware()->Container()->get('models')->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById(1);
        $shop->registerResources();
        $context =  Context::createFromShop($shop, Shopware()->Container()->get('config'));

        // Beispiel: Context dem Router zuweisen
        Shopware()->Container()->get('router')->setContext($context);

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
*/

        /**
         * @var MappingServiceInterface $mapping
         */
        $mapping = Shopware()->Container()->get('plenty_connector.mapping_service');
        $mapping->getMappingInformation(VatRate::TYPE);


        try {
            //$this->createAddress();
            //$this->createOrder();

        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }

    /**
     *
     */
    private function createOrder()
    {
        $address = [
            'addressRelations'=> [
                [
                    'typeId'=>   1,
                    'addressId'=>    291
                ],
                [
                    'typeId'=>   2,
                    'addressId'=>    815
                ]
            ],
        ];

        // TODO: Search for contact /rest/accounts/contacts params contactEmail
        $customer = $this->getCustomer();


        $params = [
            'typeId'=>   1,
            'plentyId'=> 1000,
            'relations'=> [
                [
                    'referenceType'=>    'contact',
                    'referenceId'=>  399,
                    'relation'=> 'receiver'
                ]
            ],
            'properties'=> [
                [
                    'typeId'=>   2,
                    'value'=>    '6'
                ],
                [
                    'typeId'=>   6,
                    'value'=>    'en'
                ],
                [
                    'typeId'=>   7,
                    'subTypeId'=>    6,
                    'value'=>    'Postman=>Order=>3'
                ]
            ],
            'orderItems'=> [
                [
                    'typeId'=>   1,
                    'referrerId'=>   0.0,
                    'itemVariationId'=>  1357,
                    'quantity'=> 1,
                    'orderItemName'=> 'Vincent van Gogh - Sternenhimmel und Zypresse c86462 50x60cm Ölbild handgemalt',
                    'shippingProfileId'=>    6,
                    'countryVatId'=> 1,
                    'vatField'=> 0,
                    'properties'=> [
                        [
                            'typeId'=>   4,
                            'subTypeId'=>    1,
                            'value'=>    '890'
                        ]
                    ],
                    'amounts'=> [
                        [
                            'currency'=> 'EUR',
                            'exchangeRate'=> 1.0,
                            'priceOriginalGross'=>   59.90,
                            'discount'=> 10.0,
                            'isPercentage'=> true
                        ]
                    ]
                ],
                [
                    'typeId'=>  1 ,
                    'referrerId'=>   0.0,
                    'itemVariationId'=>  1178,
                    'quantity'=> 2,
                    'orderItemName'=> 'Adidas Copa Mundial Fußballschuhe schwarz weiß Klassiker',
                    'shippingProfileId'=>    6,
                    'countryVatId'=> 1,
                    'vatField'=> 0,
                    'properties'=> [
                        [
                            'typeId'=>   4,
                            'subTypeId'=>    1,
                            'value'=>    '460'
                        ]
                    ],
                    'amounts'=> [
                        [
                            'currency'=> 'EUR',
                            'exchangeRate'=> 1.0,
                            'priceOriginalGross'=>  139.95,
                            'surcharge'=>    5.00
                        ]
                    ]
                ],
                [
                    'typeId'=>  6 ,
                    'referrerId'=>   0.0,
                    'itemVariationId'=>  1000,
                    'quantity'=> 1,
                    'orderItemName'=> 'Versandkosten 19%',
                    'countryVatId'=> 1,
                    'vatField'=> 0,
                    'amounts'=> [
                        [
                            'currency'=> 'EUR',
                            'exchangeRate'=> 1.0,
                            'priceOriginalGross'=>  4.95
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createAddress()
    {
        $address = $this->getAddress();


        try {
            $splitted = AddressSplitter::splitAddress($address->getStreet());

            $street = $splitted['streetName'];
            $houseNumber = $splitted['houseNumber'];
            $additional = trim($splitted['additionToAddress1'] . ' ' . $splitted['additionToAddress2']);
        } catch (\Exception $exception) {
            $street = $address->getStreet();
            $houseNumber = '';
            $additional = '';
        }

        $countryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $address->getCountryIdentifier(),
            'objectType' => Country::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $params = [
            'typeId' => 1, // billing
            'orderId' => 1,
            'address' => [
                'companyName' => $address->getCompany(),
                'firstName' => $address->getFirstname(),
                'lastName' => $address->getLastname(),
                'street' => $street,
                'houseNumber' => $houseNumber,
                'additional' => $additional,
                'postalCode' => $address->getZipcode(),
                'town' => $address->getCity(),
                'countryId' => $countryIdentity->getAdapterIdentifier(),
            ]
        ];
        $this->client->request('POST', 'orders/addresses', $params);
    }

    /**
     * @return Address
     */
    private function getAddress()
    {
        static $country;

        if (null === $country) {
            $countries = $this->client->request('GET', 'orders/shipping/countries');

            $country = array_shift($countries);
        }

        $countryIdentity = $this->identityService->findOneOrCreate(
            (string) $country['id'],
            PlentymarketsAdapter::NAME,
            Country::TYPE
        );

        $address = new Address();
        $address->setCompany('Company');
        $address->setDepartment('Department');
        $address->setSalutation('Salutation');
        $address->setTitle('Title');
        $address->setFirstname('Firstname');
        $address->setLastname('Lastname');
        $address->setStreet('Street 2');
        $address->setZipcode('12345');
        $address->setCity('City');
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setVatId('');

        return $address;
    }


    /**
     * @return Customer
     */
    private function getCustomer()
    {
        $customer = new Customer();
        $customer->setNumber('2000');
        $customer->setEmail('max@muster.com');
        $customer->setLanguageIdentifier($this->getLanguageIdentifier());
        $customer->setCompany('Company');
        $customer->setDepartment('Department');
        $customer->setSalutation('Salutation');
        $customer->setTitle('Title');
        $customer->setFirstname('Firstname');
        $customer->setLastname('Lastname');

        return $customer;
    }
}
