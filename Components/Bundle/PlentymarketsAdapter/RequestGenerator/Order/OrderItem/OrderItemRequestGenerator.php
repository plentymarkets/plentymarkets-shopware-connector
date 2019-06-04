<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PlentymarketsAdapter\RequestGenerator\Order\OrderItem\OrderItemRequestGeneratorInterface;
use RuntimeException;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\VatRate\VatRate;

class OrderItemRequestGenerator implements OrderItemRequestGeneratorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var OrderItemRequestGeneratorInterface
     */
    private $parentOrderItemRequestGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService,
        OrderItemRequestGeneratorInterface $parentOrderItemRequestGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
        $this->parentOrderItemRequestGenerator = $parentOrderItemRequestGenerator;
    }

    /**
     * @param OrderItem $orderItem
     * @param Order     $order
     *
     * @return array
     */
    public function generate(OrderItem $orderItem, Order $order): array
    {
        $itemParams = $this->parentOrderItemRequestGenerator->generate($orderItem, $order);

        $customerGroupIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getCustomer()->getCustomerGroupIdentifier(),
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => CustomerGroup::TYPE,
        ]);

        if (null === $customerGroupIdentity) {
            throw new RuntimeException('could not find customer group identity of bundle');
        }

        $bundle = $this->getBundle($orderItem->getNumber(), $customerGroupIdentity->getAdapterIdentifier());

        if (empty($bundle)) {
            return $itemParams;
        }

        $vatIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $orderItem->getVatRateIdentifier(),
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $vatIdentity) {
            throw new RuntimeException('could not find vat identity of bundle');
        }

        /**
         * @var Repository $taxRepository
         */
        $taxRepository = $this->entityManager->getRepository(Tax::class);

        /**
         * @var null|Tax $taxModel
         */
        $taxModel = $taxRepository->find($vatIdentity->getAdapterIdentifier());

        if (null === $taxModel) {
            throw new RuntimeException('could not find shopware vat model of bundle');
        }

        $itemParams['orderItemName'] = $bundle['name'];

        $itemParams['amounts'][0]['priceOriginalGross'] = $bundle['price'] * (1 + ($taxModel->getTax() / 100));

        return $itemParams;
    }

    /**
     * @param int $articleNumber
     * @param int $customerGroupId
     *
     * @return array
     */
    private function getBundle($articleNumber, $customerGroupId): array
    {
        try {
            $query = '
                SELECT * FROM s_articles_bundles AS bundle 
                LEFT JOIN s_articles_bundles_prices AS bundlePrice
                ON bundle.id = bundlePrice.bundle_id
                WHERE bundle.ordernumber = :articleNumber
                AND bundlePrice.customer_group_id = :customerGroupId
            ';

            $bundle = $this->entityManager->getConnection()->fetchAssoc($query, [
                ':articleNumber' => $articleNumber,
                ':customerGroupId' => $customerGroupId,
            ]);
        } catch (Exception $exception) {
            return [];
        }

        if ($bundle) {
            return $bundle;
        }

        return [];
    }
}
