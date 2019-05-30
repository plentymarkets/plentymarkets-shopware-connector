<?php

namespace PlentyConnector\Components\AmazonPay\Plentymarkets;

use PlentyConnector\Components\AmazonPay\PaymentData\AmazonPayPaymentData;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Payment\Payment;

class HandleAmazonPayPaymentCommandHandler implements CommandHandlerInterface
{
    /**
     * @var CommandHandlerInterface
     */
    private $parentCommandHandler;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(
        CommandHandlerInterface $parentCommandHandler,
        ClientInterface $client,
        IdentityServiceInterface $identityService
    ) {
        $this->parentCommandHandler = $parentCommandHandler;
        $this->client = $client;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === PlentymarketsAdapter::NAME &&
            $command->getObjectType() === Payment::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @var TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var Payment $payment
         */
        $payment = $command->getPayload();

        /**
         * @var AmazonPayPaymentData $data
         */
        $data = $payment->getPaymentData();

        if (!($data instanceof AmazonPayPaymentData)) {
            return $this->parentCommandHandler->handle($command);
        }

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            throw new NotFoundException('could not find order for amazon payment handling - ' . $payment->getOrderIdentifier());
        }

        $amazonPayDataParams = [
            'key' => $data->getKey(),
            'order_reference_id' => $data->getTransactionId(),
            'order_id' => $orderIdentity->getAdapterIdentifier(),
        ];

        $this->client->request('POST', 'amazon-shopware-connect', $amazonPayDataParams, null, null, ['foreign' => true]);

        return true;
    }
}
