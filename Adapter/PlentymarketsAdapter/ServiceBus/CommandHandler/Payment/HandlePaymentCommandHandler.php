<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Payment;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Payment\HandlePaymentCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use Psr\Log\LoggerInterface;

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
     * @var PaymentRequestGeneratorInterface
     */
    private $requestGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandlePaymentCommandHandler constructor.
     *
     * @param ClientInterface                  $client
     * @param IdentityServiceInterface         $identityService
     * @param PaymentRequestGeneratorInterface $requestGenerator
     * @param LoggerInterface                  $logger
     */
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

        if (null !== $identity) {
            return true;
        }

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifer(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            $this->logger->notice('order was not exported before payment handling');

            return false;
        }

        $params = $this->requestGenerator->generate($payment);

        $paymentResult = $this->client->request('POST', 'payments', $params);

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
