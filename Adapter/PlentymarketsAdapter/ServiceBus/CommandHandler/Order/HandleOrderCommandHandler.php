<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Order;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Order\OrderRequestGeneratorInterface;
use RuntimeException;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Order\Comment\Comment;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Shop\Shop;

class HandleOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var OrderRequestGeneratorInterface
     */
    private $orderRequestGenerator;

    public function __construct(
        ClientInterface $client,
        IdentityServiceInterface $identityService,
        OrderRequestGeneratorInterface $orderRequestGenerator
    ) {
        $this->client = $client;
        $this->identityService = $identityService;
        $this->orderRequestGenerator = $orderRequestGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === PlentymarketsAdapter::NAME &&
            $command->getObjectType() === Order::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * @param CommandInterface $command
     *
     * @throws NotFoundException
     *
     * @return bool
     */
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Order $order
         */
        $order = $command->getPayload();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if ($identity !== null) {
            return true;
        }

        if ($this->isExistingOrder($order)) {
            return true;
        }

        $result = $this->handleOrder($order);

        if ($result) {
            $this->handleComments($order);
        }

        return true;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    private function handleOrder(Order $order): bool
    {
        $params = $this->orderRequestGenerator->generate($order);
        $result = $this->client->request('post', 'orders', $params);

        $this->identityService->insert(
            $order->getIdentifier(),
            Order::TYPE,
            (string) $result['id'],
            PlentymarketsAdapter::NAME
        );

        return true;
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundException
     *
     * @return bool
     */
    private function isExistingOrder(Order $order): bool
    {
        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShopIdentifier(),
            'objectType' => Shop::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $shopIdentity) {
            throw new NotFoundException('shop not mapped');
        }

        $result = $this->client->request('GET', 'orders', [
            'externalOrderId' => $order->getOrderNumber(),
        ]);

        $result = array_filter($result, static function (array $order) use ($shopIdentity) {
            return (int) $order['plentyId'] === $shopIdentity->getAdapterIdentifier();
        });

        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundException
     */
    private function handleComments(Order $order)
    {
        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            throw new NotFoundException('could not find order for comment handling - ' . $order->getIdentifier());
        }

        foreach ($order->getComments() as $comment) {
            $commentParams = [
                'referenceType' => 'order',
                'referenceValue' => $orderIdentity->getAdapterIdentifier(),
                'text' => $comment->getComment(),
                'isVisibleForContact' => $comment->getType() === Comment::TYPE_CUSTOMER,
            ];

            if ($comment->getType() === Comment::TYPE_INTERNAL) {
                $commentParams['userId'] = $this->getUserId();
            }

            $this->client->request('post', 'comments', $commentParams);
        }
    }

    /**
     * @return int
     */
    private function getUserId(): int
    {
        static $user = null;

        if (null === $user) {
            $user = $this->client->request('GET', 'user');

            if (empty($user)) {
                throw new RuntimeException('could not read user data');
            }
        }

        return (int) $user['id'];
    }
}
