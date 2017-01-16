<?php

namespace PlentyConnector\Connector\CommandBus\Command\Manufacturer;

use Assert\Assertion;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class HandleManufacturerCommand.
 */
class HandleManufacturerCommand implements CommandInterface
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
     * ImportManufacturerCommand constructor.
     *
     * @param string $adapterName the classname of the target adapter
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
