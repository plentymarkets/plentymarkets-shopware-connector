<?php

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\MappingService\MappingServiceInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Mapping\MappingInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\PlentyConnector;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

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

        $this->View()->assign(array(
            'success' => $success,
        ));
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

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->Request()->getParams(),
        ));
    }

    /**
     * @throws \Exception
     */
    public function getSettingsListAction()
    {
        $config = $this->container->get('plenty_connector.config');

        $this->View()->assign(array(
            'success' => true,
            'data' => [
                'ApiUrl' => $config->get('rest_url'),
                'ApiUsername' => $config->get('rest_username'),
                'ApiPassword' => $config->get('rest_password'),
            ],
        ));
    }

    /**
     * @throws \Exception
     */
    public function getMappingInformationAction()
    {
        $fresh = $this->request->get('fresh') === 'true';

        /**
         * @var MappingServiceInterface $mappingService
         */
        $mappingService = Shopware()->Container()->get('plenty_connector.mapping_service');

        try {
            $mappingInformation = $mappingService->getMappingInformation(null, $fresh);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage()
            ]);

            return;
        }

        $transferObjectMapping = function (MappedTransferObjectInterface $object) {
            return [
                'identifier' => $object->getIdentifier(),
                'type' => $object->getType(),
                'name' => $object->getName()
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
                    'objectType' => $mapping->getObjectType()
                ];
            }, $mappingInformation)
        ]);
    }

    /**
     * @throws \Exception
     */
    public function updateIdentitiesAction()
    {
        $updates = json_decode($this->request->getRawBody());

        if (!is_array($updates)) {
            $updates = [$updates];
        }

        /**
         * @var IdentityService $identityService
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        try {
            foreach ($updates as $update) {
                $originAdapterName = $update->originAdapterName;
                $originIdentifier = $update->originIdentifier;
                $destinationIdentifier = $update->identifier;
                $objectType = $update->objectType;

                $oldIdentity = $identityService->findOneBy([
                    'objectType' => $objectType,
                    'objectIdentifier' => $originIdentifier,
                    'adapterName' => $originAdapterName,
                ]);

                $originAdapterIdentifier = $oldIdentity->getAdapterIdentifier();

                $identityService->remove(Identity::fromArray([
                    'objectIdentifier' => $originIdentifier,
                    'objectType' => $objectType,
                    'adapterIdentifier' => $originAdapterIdentifier,
                    'adapterName' => $originAdapterName,
                ]));

                $identityService->create(
                    $destinationIdentifier,
                    $objectType,
                    $originAdapterIdentifier,
                    $originAdapterName
                );
            }

            $this->View()->assign([
                'success' => true,
                'data' => $updates
            ]);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage()
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
                'message' => 'Artikel ID ist leer.'
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
                'success' => true
            ]);
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
