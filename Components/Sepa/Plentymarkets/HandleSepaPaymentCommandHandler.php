<?php

namespace PlentyConnector\Components\Sepa\Plentymarkets;

use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Payment\HandlePaymentCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class HandleSepaPaymentCommandHandler.
 */
class HandleSepaPaymentCommandHandler implements CommandHandlerInterface
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

    /**
     * HandleSepaPaymentCommandHandler constructor.
     *
     * @param CommandHandlerInterface  $parentCommandHandler
     * @param ClientInterface          $client
     * @param IdentityServiceInterface $identityService
     */
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

        if (!($payment->getPaymentData() instanceof SepaPaymentData)) {
            return $this->parentCommandHandler->handle($command);
        }

        /**
         * @var SepaPaymentData $data
         */
        $data = $payment->getPaymentData();

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifer(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $orderIdentity) {
            throw new NotFoundException('could not find order for bank account handling - ' . $payment->getOrderIdentifer());
        }

        $contactId = $this->getContactIdentifier($orderIdentity);

        if (null === $contactId) {
            throw new NotFoundException('could not find contact for bank account handling - ' . $payment->getOrderIdentifer());
        }

        $bankAccounts = $this->client->request('GET', 'accounts/contacts/' . $contactId . '/banks');

        $possibleBankAccounts = array_filter($bankAccounts, function (array $bankAccount) use ($data, $orderIdentity) {
            return $bankAccount['iban'] === $data->getIban() && $bankAccount['orderId'] === (int) $orderIdentity->getAdapterIdentifier();
        });

        if (!empty($possibleBankAccounts)) {
            return true;
        }

        $sepaPaymentDataParams = [
            'lastUpdateBy' => 'import',
            'accountOwner' => $data->getAccountOwner(),
            'iban' => $data->getIban(),
            'bic' => $data->getBic(),
            'orderId' => $orderIdentity->getAdapterIdentifier(),
            'contactId' => $contactId,
        ];

        $this->client->request('POST', 'accounts/contacts/banks', $sepaPaymentDataParams);

        return true;
    }

    /**
     * @param Identity $orderIdentity
     *
     * @return null|int
     */
    private function getContactIdentifier(Identity $orderIdentity)
    {
        $order = $this->client->request('GET', 'orders/' . $orderIdentity->getAdapterIdentifier());

        $relations = array_filter($order['relations'], function (array $relation) {
            return $relation['referenceType'] === 'contact';
        });

        if (empty($relations)) {
            return null;
        }

        $contactRelation = array_shift($relations);

        return $contactRelation['referenceId'];
    }
}
