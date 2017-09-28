<?php

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\MappingService\MappingServiceInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Mapping\Mapping;
use PlentyConnector\PlentyConnector;
use PlentymarketsAdapter\Client\ClientInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class Shopware_Controllers_Backend_PlentyConnector
 */
class Shopware_Controllers_Backend_PlentyConnector extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * initialize permissions per action
     */
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

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    public function saveSettingsAction()
    {
        /**
         * @var ConfigServiceInterface $config
         */
        $config = $this->container->get('plenty_connector.config');

        foreach ($this->cleanParameters($this->Request()->getParams()) as $key => $value) {
            $config->set($key, $value);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function getSettingsListAction()
    {
        /**
         * @var ConfigServiceInterface $config
         */
        $config = $this->container->get('plenty_connector.config');

        $this->View()->assign([
            'success' => true,
            'data' => $config->getAll(),
        ]);
    }

    /**
     * @throws \Exception
     */
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

        $transferObjectMapping = function (TransferObjectInterface $object) {
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
            'data' => array_map(function (Mapping $mapping) use ($transferObjectMapping) {
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

    /**
     * @throws \Exception
     */
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

                $destinationAdapterIdentifier = $oldDestinationIdentity->getAdapterIdentifier();
                $identityService->remove($oldDestinationIdentity);

                $newIdentifier = $remove ? Uuid::uuid4()->toString() : $originIdentifier;
                $identityService->create(
                    $newIdentifier,
                    $objectType,
                    $destinationAdapterIdentifier,
                    $destinationAdapterName
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

        /**
         * @var Shopware_Components_Snippet_Manager $snippetManager
         */
        $snippetManager = $this->container->get('snippets');
        $namespace = 'backend/plentyconnector/main';
        $snippet = 'plentyconnector/view/settings/additional/item_warehouse/virtualWarehouse';

        $data = [
            [
                'id' => 0,
                'name' => $snippetManager->getNamespace($namespace)->get($snippet),
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

    private function cleanParameters(array $params)
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
}
