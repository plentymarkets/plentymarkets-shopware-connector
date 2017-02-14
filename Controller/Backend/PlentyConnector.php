<?php

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\MappingService\MappingServiceInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Mapping\MappingInterface;
use PlentyConnector\PlentyConnector;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
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
        $client = $this->container->get('plentmarkets_adapter.client');

        $params = [
            'username' => $this->Request()->get('ApiUsername'),
            'password' => $this->Request()->get('ApiPassword'),
        ];

        $options = [
            'base_uri' => $this->Request()->get('ApiUrl'),
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

        $config->set('rest_url', $this->Request()->get('ApiUrl'));
        $config->set('rest_username', $this->Request()->get('ApiUsername'));
        $config->set('rest_password', $this->Request()->get('ApiPassword'));

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
        $config = $this->container->get('plenty_connector.config');

        $this->View()->assign([
            'success' => true,
            'data' => [
                'ApiUrl' => $config->get('rest_url'),
                'ApiUsername' => $config->get('rest_username'),
                'ApiPassword' => $config->get('rest_password'),
            ],
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
        $mappingService = Shopware()->Container()->get('plenty_connector.mapping_service');

        try {
            $mappingInformation = $mappingService->getMappingInformation(null);
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
            'data' => array_map(function (MappingInterface $mapping) use ($transferObjectMapping) {
                return [
                    'originAdapterName' => $mapping->getOriginAdapterName(),
                    'destinationAdapterName' => $mapping->getDestinationAdapterName(),
                    'originTransferObjects' => array_map($transferObjectMapping, $mapping->getOriginTransferObjects()),
                    'destinationTransferObjects' => array_map($transferObjectMapping,
                        $mapping->getDestinationTransferObjects()),
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
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        try {
            if ($this->hasDuplicateMappings($updates)) {
                $this->View()->assign([
                    'success' => false,
                    'message' => 'duplicate mapping',
                ]);

                return;
            }

            foreach ($updates as $key => $update) {
                $remove = $update['remove'];

                $objectType = $update['objectType'];
                $destinationAdapterName = $update['adapterName'];
                $destinationIdentifier = $update['identifier'];
                $originIdentifier = $update['originIdentifier'];

                $oldDestinationIdentity = $identityService->findOneBy([
                    'objectType' => $objectType,
                    'objectIdentifier' => $destinationIdentifier,
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

    /**
     * TODO: Remove identity if nothing has been handled
     *
     * Sync one product based on the plentymarkets id
     */
    public function syncItemAction()
    {
        $data = json_decode($this->request->getRawBody(), true);

        if (null === $data['itemId'] || '' === $data['itemId']) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Artikel ID ist leer.',
            ]);

            return;
        }

        try {
            /**
             * @var IdentityServiceInterface $identityService
             */
            $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

            $identity = $identityService->findOneOrCreate(
                $data['itemId'],
                PlentymarketsAdapter::NAME,
                Product::TYPE
            );

            /**
             * @var ConnectorInterface $connector
             */
            $connector = Shopware()->Container()->get('plenty_connector.connector');
            $connector->handle(QueryType::ONE, Product::TYPE, $identity->getObjectIdentifier());

            $this->View()->assign([
                'success' => true,
            ]);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param array $updates
     *
     * @throws Exception
     *
     * @return bool
     */
    private function hasDuplicateMappings(array $updates)
    {
        $originIdentifiers = array_column(array_filter($updates, function ($update) {
            return !$update['remove'];
        }), 'originIdentifier');

        if (count(array_count_values($originIdentifiers)) < count($originIdentifiers)) {
            return true;
        }

        /**
         * @var IdentityService $identityService
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        foreach ($updates as $key => $update) {
            $existingDestinationIdentities = $identityService->findby([
                'objectType' => $update['objectType'],
                'objectIdentifier' => $update['originIdentifier'],
                'adapterName' => $update['adapterName'],
            ]);

            if (null !== $existingDestinationIdentities && count($existingDestinationIdentities) > 0) {
                return true;
            }
        }

        return false;
    }
}
