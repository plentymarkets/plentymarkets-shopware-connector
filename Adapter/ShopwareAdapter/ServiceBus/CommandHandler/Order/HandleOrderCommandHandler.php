<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Order;

use DateTime;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\Package\Package;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Models\Order\Status;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;

class HandleOrderCommandHandler implements CommandHandlerInterface
{
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

    /**
     * HandleOrderCommandHandler constructor.
     *
     * @param IdentityServiceInterface        $identityService
     * @param LoggerInterface                 $logger
     * @param AttributeDataPersisterInterface $attributePersister
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        AttributeDataPersisterInterface $attributePersister
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->attributePersister = $attributePersister;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
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
    public function handle(CommandInterface $command)
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

        $params = [
            'details' => [],
        ];

        $package = $this->getPackage($order);

        if (null !== $package) {
            $this->addShippingProviderAttribute($order, $package);

            $params['trackingCode'] = $package->getShippingCode();
        }

        $orderStatusIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getOrderStatusIdentifier(),
            'objectType' => OrderStatus::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null !== $orderStatusIdentity) {
            $params['orderStatusId'] = $orderStatusIdentity->getAdapterIdentifier();
        } else {
            $this->logger->notice('order status not mapped', ['order' => $order]);
        }

        $paymentStatusIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getPaymentStatusIdentifier(),
            'objectType' => PaymentStatus::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null !== $paymentStatusIdentity) {
            $params['paymentStatusId'] = $paymentStatusIdentity->getAdapterIdentifier();

            if ((int) $paymentStatusIdentity->getAdapterIdentifier() === Status::PAYMENT_STATE_COMPLETELY_PAID) {
                $params['cleareddate'] = new DateTime('now');
            }
        } else {
            $this->logger->notice('payment status not mapped', ['order' => $order]);
        }

        $resource = $this->getOrderResource();
        $orderModel = $resource->update($orderIdentity->getAdapterIdentifier(), $params);

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

    /**
     * @return OrderResource
     */
    private function getOrderResource()
    {
        // without this reset the entitymanager sometimes the status is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Order');
    }
}
