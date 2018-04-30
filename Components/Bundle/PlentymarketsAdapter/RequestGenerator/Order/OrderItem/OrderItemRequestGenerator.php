<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\RequestGenerator\Order\OrderItem\OrderItemRequestGeneratorInterface;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ShopwareAdapter;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class OrderItemRequestGenerator
 */
class OrderItemRequestGenerator implements OrderItemRequestGeneratorInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var OrderItemRequestGeneratorInterface
     */
    private $parentOrderItemRequestGenerator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * OrderItemRequestGenerator constructor.
     *
     * @param EntityManagerInterface             $entityManager
     * @param IdentityServiceInterface           $identityService
     * @param OrderItemRequestGeneratorInterface $parentOrderItemRequestGenerator
     * @param Connection                         $connection
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService,
        OrderItemRequestGeneratorInterface $parentOrderItemRequestGenerator,
        Connection $connection
    ) {
        $this->repository = $entityManager->getRepository(Tax::class);
        $this->identityService = $identityService;
        $this->parentOrderItemRequestGenerator = $parentOrderItemRequestGenerator;
        $this->connection = $connection;
    }

    /**
     * @param OrderItem $orderItem
     * @param Order     $order
     *
     * @return array
     */
    public function generate(OrderItem $orderItem, Order $order)
    {
        $itemParams = $this->parentOrderItemRequestGenerator->generate($orderItem, $order);

        $customerGroupIdentity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => $order->getCustomer()->getCustomerGroupIdentifier(),
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => CustomerGroup::TYPE,
            ]
        );

        $bundle = $this->getBundle($orderItem->getNumber(), $customerGroupIdentity->getAdapterIdentifier());

        if (empty($bundle)) {
            return $itemParams;
        }

        $vatIdentity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => $orderItem->getVatRateIdentifier(),
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => VatRate::TYPE,
            ]
        );

        /**
         * @var Tax $taxModel
         */
        $taxModel = $this->repository->find($vatIdentity->getAdapterIdentifier());

        $itemParams['orderItemName'] = $bundle[0]['name'];

        $itemParams['amounts'][0]['priceOriginalGross'] = $bundle[0]['price'] * (1 + ($taxModel->getTax() / 100));

        return $itemParams;
    }

    /**
     * @param int $articleNumber
     *
     * @return array
     */
    private function getBundle($articleNumber, $customerGroupId)
    {
        try {
            $query = 'SELECT * FROM s_articles_bundles AS bundle 
                      LEFT JOIN s_articles_bundles_prices AS bundlePrice
                      ON bundle.id = bundlePrice.bundle_id
                      WHERE bundle.ordernumber = :articleNumber
                      AND bundlePrice.customer_group_id = :customerGroupId';

            return $this->connection->fetchAll($query, [':articleNumber' =>$articleNumber, ':customerGroupId' => $customerGroupId]);
        } catch (Exception $exception) {
            return [];
        }
    }
}
