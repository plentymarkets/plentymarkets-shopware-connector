<?php

use PlentyConnector\Connector\QueryBus\Query\Manufacturer\GetManufacturerQuery;
use PlentyConnector\Infrastructure\QueryBus\Query\GetRemoteOrderReferrerQuery;
use PlentyConnector\Infrastructure\QueryBus\Query\GetRemoteWarehouseQuery;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Shopware\Components\Api\Manager;

/**
 * Class Shopware_Controller_Backend_Plentymarkets.
 */
class Shopware_Controllers_Backend_Plentymarkets extends Shopware_Controllers_Backend_ExtJs
{
    public function testApiCredentialsAction()
    {
        $config = $this->container->get('plentyconnector.config');

        $apiUrl = $config->get('rest_url');
        $apiUsername = $config->get('rest_username');
        $apiPassword = $config->get('rest_password');

        $config->set('rest_url', $this->Request()->get('ApiUrl'));
        $config->set('rest_username', $this->Request()->get('ApiUsername'));
        $config->set('rest_password', $this->Request()->get('ApiPassword'));

        $queryBus = $this->container->get('plentyconnector.query_bus');
        try {
            // do sample request to check whether the credentials are valid
            $queryBus->handle(new FetchAllManufacturersQuery(PlentymarketsAdapter::getName()));
            $success = true;
        } catch (Exception $exception) {
            $success = false;
        }

        $config->set('rest_url', $apiUrl);
        $config->set('rest_username', $apiUsername);
        $config->set('rest_password', $apiPassword);

        $this->View()->assign(array(
            'success' => $success,
        ));
    }

    public function saveSettingsAction()
    {
        $config = $this->container->get('plentyconnector.config');

        $config->set('rest_url', $this->Request()->get('ApiUrl'));
        $config->set('rest_username', $this->Request()->get('ApiUsername'));
        $config->set('rest_password', $this->Request()->get('ApiPassword'));

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->Request()->getParams(),
        ));
    }

    public function getSettingsListAction()
    {
        $data = array();
        $config = $this->container->get('plentyconnector.config');

        $data['ApiUrl'] = $config->get('rest_url');
        $data['ApiUsername'] = $config->get('rest_username');
        $data['ApiPassword'] = $config->get('rest_password');

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
        ));
    }

    /**
     * Loads stores settings.
     */
    public function getSettingsViewDataAction()
    {
        /**
         * @var Shopware\Components\Api\Resource\Manufacturer
         */
        $resource = Manager::getResource('manufacturer');
        $manufacturers = $resource->getList(0, null)['data'];

//        $queryBus = $this->container->get('plentyconnector.query_bus');
//
//        $warehouses = array_map(function(ResponseItem $item) {
//            return array(
//                'name' => $item->getItem()->getName()
//            );
//        }, $queryBus->handle(new GetRemoteWarehouseQuery()));
//
//        $orderReferrers = array_map(function(ResponseItem $item) {
//            return array(
//                'name' => $item->getItem()->getName()
//            );
//        }, $queryBus->handle(new GetRemoteOrderReferrerQuery()));

        $this->View()->assign(array(
            'success' => true,
            'data' => array(
                'manufacturers' => $manufacturers,
                'warehouses' => [],
                'orderReferrers' => [],
            ),
        ));
    }

    public function getMappingsAction()
    {
        /**
         * @var MappingServiceInterface $mappingService
         */
        $mappingService = Shopware()->Container()->get('plentyconnector.mapping_service');
        $mappingInformation = $mappingService->getMappingInformation();

        $transferObjectMapping = function($object) {
            /**
             * @var MappedTransferObjectInterface $object
             */
            return [
                'identifier' => $object->getIdentifier(),
                'type' => $object::getType(),
                'name' => $object->getName()
            ];
        };

        $this->View()->assign([
            'success' => true,
            'data' => array_map(function($mapping) use ($transferObjectMapping) {
                /**
                 * @var MappingInterface $mapping
                 */
                return [
                    'originAdapterName' => $mapping->getOriginAdapterName(),
                    'destinationAdapterName' => $mapping->getDestinationAdapterName(),
                    'originTransferObjects' => array_map($transferObjectMapping, $mapping->getOriginTransferObjects()),
                    'destinationTransferObjects' => array_map($transferObjectMapping, $mapping->getDestinationTransferObjects()),
                    'isComplete' => $mapping->isIsComplete()
                ];
            }, $mappingInformation)
        ]);
    }

    public function updateIdentityAction()
    {

    }
}
