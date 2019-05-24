<?php

use PlentyConnector\PlentyConnector;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Ramsey\Uuid\Uuid;
use SystemConnector\BacklogService\Middleware\BacklogCommandHandlerMiddleware;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\ConnectorInterface;
use SystemConnector\IdentityService\IdentityService;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\MappingService\MappingServiceInterface;
use SystemConnector\MappingService\Struct\Mapping;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\TransferObjectInterface;

class Shopware_Controllers_Backend_PlentyConnector extends Shopware_Controllers_Backend_ExtJs
{
    public function initAcl()
    {
        // Credentials
        $this->addAclPermission('testApiCredentials', PlentyConnector::PERMISSION_READ, 'Insufficient Permissions');

        // Settings
        $this->addAclPermission('getSettingsList', PlentyConnector::PERMISSION_READ, 'Insufficient Permissions');
        $this->addAclPermission('saveSettings', PlentyConnector::PERMISSION_WRITE, 'Insufficient Permissions');

        // Mapping
        $this->addAclPermission('getMappingInformation', PlentyConnector::PERMISSION_READ, 'Insufficient Permissions');
        $this->addAclPermission('updateIdentities', PlentyConnector::PERMISSION_WRITE, 'Insufficient Permissions');

        // Sync one product
        $this->addAclPermission('syncItem', PlentyConnector::PERMISSION_WRITE, 'Insufficient Permissions');
    }

    public function testApiCredentialsAction()
    {
        /**
         * @var ClientInterface $client
         */
        $client = $this->container->get('plentymarkets_adapter.client');

        $params = [
            'username' => $this->Request()->get('rest_username'),
            'password' => $this->Request()->get('rest_password'),
        ];

        $options = [
            'base_uri' => $this->Request()->get('rest_url'),
        ];

        $success = false;

        try {
            $login = $client->request('POST', 'login', $params, null, null, $options);

            if (isset($login['accessToken'])) {
                $success = true;
            }
        } catch (Exception $exception) {
            // fail silently
        }

        $this->View()->assign([
            'success' => $success,
        ]);
    }

    public function saveSettingsAction()
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = $this->container->get('plenty_connector.config_service');

        foreach ($this->cleanParameters($this->Request()->getParams()) as $key => $value) {
            $configService->set($key, $value);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    public function getSettingsListAction()
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = $this->container->get('plenty_connector.config_service');

        $this->View()->assign([
            'success' => true,
            'data' => $configService->getAll(),
        ]);
    }

    public function getMappingInformationAction()
    {
        /**
         * @var MappingServiceInterface $mappingService
         */
        $mappingService = $this->container->get('plenty_connector.mapping_service');

        try {
            $mappingInformation = $mappingService->getMappingInformation();
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        $transferObjectMapping = static function (TransferObjectInterface $object) {
            if (method_exists($object, 'getName')) {
                $name = $object->getName();
            } else {
                $name = $object->getIdentifier();
            }

            return [
                'identifier' => $object->getIdentifier(),
                'type' => $object->getType(),
                'name' => $name,
            ];
        };

        $this->View()->assign([
            'success' => true,
            'data' => array_map(
                static function (Mapping $mapping) use ($transferObjectMapping) {
                    return [
                    'originAdapterName' => $mapping->getOriginAdapterName(),
                    'destinationAdapterName' => $mapping->getDestinationAdapterName(),
                    'originTransferObjects' => array_map($transferObjectMapping, $mapping->getOriginTransferObjects()),
                    'destinationTransferObjects' => array_map($transferObjectMapping, $mapping->getDestinationTransferObjects()),
                    'objectType' => $mapping->getObjectType(),
                ];
                }, $mappingInformation),
        ]);
    }

    public function updateIdentitiesAction()
    {
        $updates = json_decode($this->request->getRawBody(), true);

        if (array_key_exists('identifier', $updates)) {
            $updates = [$updates];
        }

        /**
         * @var IdentityService $identityService
         */
        $identityService = $this->container->get('plenty_connector.identity_service');

        try {
            foreach ($updates as $key => $update) {
                $remove = $update['remove'];

                $objectType = $update['objectType'];
                $destinationAdapterName = $update['adapterName'];
                $destinationIdentifier = $update['identifier'];
                $originIdentifier = $update['originIdentifier'];

                $oldDestinationIdentity = $identityService->findOneBy([
                    'objectIdentifier' => $destinationIdentifier,
                    'objectType' => $objectType,
                    'adapterName' => $destinationAdapterName,
                ]);

                if (null === $oldDestinationIdentity) {
                    $this->View()->assign([
                        'success' => false,
                        'message' => 'reload mapping',
                    ]);

                    return;
                }

                $newIdentifier = $remove ? Uuid::uuid4()->toString() : $originIdentifier;

                $identityService->update(
                    $oldDestinationIdentity,
                    [
                        'objectIdentifier' => $newIdentifier,
                    ]
                );

                $updates[$key]['identifier'] = $newIdentifier;
                if ($remove) {
                    $updates[$key]['originAdapterName'] = null;
                    $updates[$key]['originIdentifier'] = null;
                    $updates[$key]['remove'] = false;
                }
            }

            $this->View()->assign([
                'success' => true,
                'data' => $updates,
            ]);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function getOrderOriginsAction()
    {
        /**
         * @var ClientInterface $client
         */
        $client = $this->container->get('plentymarkets_adapter.client');

        $data = [];

        try {
            foreach ($client->request('GET', 'orders/referrers') as $origin) {
                $data[] = [
                    'id' => $origin['id'],
                    'name' => $origin['name'],
                ];
            }
        } catch (Exception $exception) {
            // fail silently
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getItemWarehousesAction()
    {
        /**
         * @var ClientInterface $client
         */
        $client = $this->container->get('plentymarkets_adapter.client');

        $data = [
            [
                'id' => 0,
                'name' => $this->getTranslation(
                    'plentyconnector/view/settings/additional/item_warehouse/virtualWarehouse'
                ),
            ],
        ];

        try {
            foreach ($client->request('GET', 'stockmanagement/warehouses') as $origin) {
                $data[] = [
                    'id' => $origin['id'],
                    'name' => $origin['name'],
                ];
            }
        } catch (Exception $exception) {
            // fail silently
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function syncItemAction()
    {
        $itemId = (int) $this->Request()->get('item_id');

        if (empty($itemId)) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->getTranslation('plentyconnector/controller/actions/item_import/missing_item_id'),
            ]);

            return;
        }

        try {
            /**
             * @var IdentityServiceInterface $identityService
             */
            $identityService = $this->container->get('plenty_connector.identity_service');

            /**
             * @var ConnectorInterface $connector
             */
            $connector = $this->container->get('plenty_connector.connector');

            /**
             * @var ClientInterface $client
             */
            $client = $this->container->get('plentymarkets_adapter.client');

            try {
                $client->request('GET', 'items/' . $itemId);
            } catch (Exception $exception) {
                $this->View()->assign([
                    'success' => false,
                    'message' => $this->getTranslation('plentyconnector/controller/actions/item_import/item_not_found'),
                ]);

                return;
            }

            $productIdentity = $identityService->findOneOrCreate(
                (string) $itemId,
                PlentymarketsAdapter::NAME,
                Product::TYPE
            );

            BacklogCommandHandlerMiddleware::$active = false;

            $connector->handle(QueryType::ONE, Product::TYPE, $productIdentity->getObjectIdentifier());

            $this->View()->assign([
                'success' => true,
                'message' => '',
            ]);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function cleanParameters(array $params): array
    {
        $result = [];

        $blacklist = [
            'action',
            'controller',
            'module',
            '_dc',
        ];

        foreach ($params as $key => $value) {
            if (in_array($key, $blacklist, true)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param string $snippet
     *
     * @return string
     */
    private function getTranslation($snippet): string
    {
        /**
         * @var Shopware_Components_Snippet_Manager $snippetManager
         */
        $snippetManager = $this->container->get('snippets');
        $namespace = 'backend/plentyconnector/main';

        return $snippetManager->getNamespace($namespace)->get($snippet);
    }
}
