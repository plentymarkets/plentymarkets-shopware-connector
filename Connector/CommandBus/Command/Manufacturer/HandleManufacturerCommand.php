<?php

namespace PlentyConnector\Connector\CommandBus\Command\Manufacturer;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class HandleManufacturerCommand.
 */
class HandleManufacturerCommand implements CommandInterface
{
    /**
     * @var ManufacturerInterface
     */
    private $manufacturer;

    /**
     * @var string
     */
    private $adapterName;

    /**
     * ImportLocalManufacturerCommand constructor.
     *
     * @param TransferObjectInterface $manufacturer the transferobject which will be handeled
     * @param string $adapterName the classname of the target adapter
     */
    public function __construct(TransferObjectInterface $manufacturer, $adapterName = '')
    {
        $this->manufacturer = $manufacturer;
        $this->adapterName = $adapterName;
    }

    /**
     * @return ManufacturerInterface
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
            'manufacturer' => $this->manufacturer,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->adapterName = $payload['adapterName'];
        $this->manufacturer = $payload['manufacturer'];
    }
}
