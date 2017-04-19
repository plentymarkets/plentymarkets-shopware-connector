<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Payment;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Payment\HandlePaymentCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class HandlePaymentCommandHandler.
 */
class HandlePaymentCommandHandler implements CommandHandlerInterface
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
     * HandlePaymentCommandHandler constructor.
     *
     * @param ClientInterface          $client
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandlePaymentCommand &&
            $command->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Payment                $payment
         */
        $payment = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getIdentifier(),
            'objectType' => Payment::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if ($identity !== null) {
            return true;
        }

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifer(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            throw new NotFoundException('order not found');
        }

        $paymentMethodIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getPaymentMethodIdentifier(),
            'objectType' => PaymentMethod::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $paymentMethodIdentity) {
            throw new NotFoundException('payment method not mapped');
        }

        $currencyIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getCurrencyIdentifier(),
            'objectType' => Currency::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $currencyIdentity) {
            throw new NotFoundException('currency not mapped');
        }

        $paymentParams = [
            'amount' => $payment->getAmount(),
            'exchangeRatio' => 1,
            'mopId' => $paymentMethodIdentity->getAdapterIdentifier(),
            'currency' => $currencyIdentity->getAdapterIdentifier(),
            'type' => 'credit',
            'transactionType' => 2,
        ];

        /**
         * Payment origin = 23
         * Name of the sender = 11
         * Email of the sender = 12
         * Transaction ID = 1
         * Booking text = 3
         * Shipping address ID = 24
         * Invoice address ID = 25
         */
        $paymentParams['property'] = [
            [
                'typeId' => 23,
                'value' => 'shopware',
            ],
            [
                'typeId' => 1,
                'value' => $payment->getTransactionReference(),
            ],
            [
                'typeId' => 3,
                'value' => 'booked',
            ],
        ];

        $paymentResult = $this->client->request(
            'POST',
            'payments',
            $paymentParams
        );

        $this->identityService->create(
            $payment->getIdentifier(),
            Payment::TYPE,
            $paymentResult['id'],
            PlentymarketsAdapter::NAME
        );

        $this->client->request(
            'POST',
            'payment/' . $paymentResult['id'] . '/order/' . $orderIdentity->getAdapterIdentifier()
        );

        return true;
    }
}
