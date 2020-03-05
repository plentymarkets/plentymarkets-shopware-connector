<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Payment;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Payment\Payment;

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
     * @var PaymentRequestGeneratorInterface
     */
    private $requestGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientInterface $client,
        IdentityServiceInterface $identityService,
        PaymentRequestGeneratorInterface $requestGenerator,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->identityService = $identityService;
        $this->requestGenerator = $requestGenerator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command): bool
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
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Payment $payment
         */
        $payment = $command->getPayload();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getIdentifier(),
            'objectType' => Payment::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null !== $identity) {
            return true;
        }

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            $this->logger->notice('order was not exported before payment handling');

            return false;
        }

        $paymentResult = $this->findOrCreatePlentyPayment($payment);

        if ((int) $orderIdentity->getAdapterIdentifier() === (int) $paymentResult['order']['orderId']) {
            return true;
        }

        $this->client->request(
            'POST',
            'payment/' . $paymentResult['id'] . '/order/' . $orderIdentity->getAdapterIdentifier()
        );

        return true;
    }

    private function findOrCreatePlentyPayment(Payment $payment): array
    {
        $plentyPayments = $this->fetchPlentyPayments($payment);

        if (!empty($plentyPayments)) {
            $paymentResult = $plentyPayments[0];
            $this->logger->debug('payment with the same transaction id "' . $paymentResult['id'] . '" already exists.');
        } else {
            $paymentResult = $this->createPlentyPayment($payment);
        }

        $this->identityService->insert(
            $payment->getIdentifier(),
            Payment::TYPE,
            (string) $paymentResult['id'],
            PlentymarketsAdapter::NAME
        );

        return $paymentResult;
    }

    /**
     * @param Payment $payment
     */
    private function fetchPlentyPayments($payment): array
    {
        $url = 'payments/property/1/' . $payment->getTransactionReference();
        $payments = $this->client->request('GET', $url);

        if (empty($payments)) {
            return [];
        }

        $payments = array_filter($payments, static function (array $payment) {
            return !$payment['deleted'];
        });

        return $payments;
    }

    private function createPlentyPayment(Payment $payment): array
    {
        $params = $this->requestGenerator->generate($payment);

        return $this->client->request('POST', 'payments', $params);
    }
}
