<?php

namespace PlentyConnector\Connector\ServiceBus\Command\Payment;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class HandlePaymentCommand.
 */
class HandlePaymentCommand implements HandleCommandInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * @var TransferObjectInterface
     */
    private $transferObject;

    /**
     * HandlePaymentCommand constructor.
     *
     * @param string                  $adapterName    the classname of the target adapter
     * @param TransferObjectInterface $transferObject the transferobject which will be handeled
     */
    public function __construct($adapterName, TransferObjectInterface $transferObject)
    {
        Assertion::string($adapterName);

        $this->adapterName = $adapterName;
        $this->transferObject = $transferObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @return TransferObjectInterface
     */
    public function getTransferObject()
    {
        return $this->transferObject;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
            'transferObject' => $this->transferObject,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->adapterName = $payload['adapterName'];
        $this->transferObject = $payload['transferObject'];
    }
}
