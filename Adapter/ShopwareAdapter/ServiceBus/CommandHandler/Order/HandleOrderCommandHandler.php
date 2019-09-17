<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Order;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Models\Order\Repository as OrderRepository;
use Shopware\Models\Order\Status;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\Package\Package;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;
use SystemConnector\ValueObject\Attribute\Attribute;

class HandleOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Status
     */
    private $statusRepository;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AttributeDataPersisterInterface
     */
    private $attributePersister;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        AttributeDataPersisterInterface $attributePersister
    ) {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->attributePersister = $attributePersister;
        $this->orderRepository = $entityManager->getRepository(OrderModel::class);
        $this->statusRepository = $entityManager->getRepository(Status::class);
    }

    /**
     * @param CommandInterface $command
     *
     * @return bool
     */
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Order::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Order $order
         */
        $order = $command->getPayload();

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            return false;
        }

        /**
         * @var OrderModel $orderModel
         */
        $orderModel = $this->orderRepository->find($orderIdentity->getAdapterIdentifier());

        if (null === $order) {
            return false;
        }

        $package = $this->getPackage($order);

        if (null !== $package) {
            $this->addShippingProviderAttribute($order, $package);

            $orderModel->setTrackingCode($package->getShippingCode());
        }

        $orderStatusIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getOrderStatusIdentifier(),
            'objectType' => OrderStatus::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null !== $orderStatusIdentity) {
            /**
             * @var Status $orderStatus
             */
            $orderStatus = $this->statusRepository->find($orderStatusIdentity->getAdapterIdentifier());
            $orderModel->setOrderStatus($orderStatus);
        } else {
            $this->logger->notice('order status not mapped', ['order' => $order]);
        }

        $paymentStatusIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getPaymentStatusIdentifier(),
            'objectType' => PaymentStatus::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null !== $paymentStatusIdentity) {
            /**
             * @var Status $paymentStatus
             */
            $paymentStatus = $this->statusRepository->find($paymentStatusIdentity->getAdapterIdentifier());
            $orderModel->setPaymentStatus($paymentStatus);

            if ((int) $paymentStatusIdentity->getAdapterIdentifier() === Status::PAYMENT_STATE_COMPLETELY_PAID) {
                $orderModel->setClearedDate(new DateTime('now'));
            }
        } else {
            $this->logger->notice('payment status not mapped', ['order' => $order]);
        }

        $this->entityManager->persist($orderModel);
        $this->entityManager->flush();

        $this->attributePersister->saveOrderAttributes(
            $orderModel,
            $order->getAttributes()
        );

        return true;
    }

    /**
     * @param Order $order
     *
     * @return null|Package
     */
    private function getPackage(Order $order)
    {
        $packages = $order->getPackages();

        if (empty($packages)) {
            return null;
        }

        return array_shift($packages);
    }

    /**
     * @param Order   $order
     * @param Package $package
     */
    private function addShippingProviderAttribute(Order $order, Package $package)
    {
        if (null === $package->getShippingProvider()) {
            return;
        }

        $attributes = $order->getAttributes();

        $shippingProvider = new Attribute();
        $shippingProvider->setKey('shippingProvider');
        $shippingProvider->setValue($package->getShippingProvider());

        $attributes[] = $shippingProvider;

        $order->setAttributes($attributes);
    }
}
