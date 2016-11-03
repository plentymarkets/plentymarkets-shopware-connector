<?php

namespace PlentyConnector\Connector\CommandBus\Command;

use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;

/**
 * Class ImportManufacturerCommand.
 */
class ImportManufacturerCommand implements CommandInterface
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
     * @param ManufacturerInterface $manufacturer the transferobject which will be handeled
     * @param string                $adapterName  the classname of the target adapter
     */
    public function __construct(ManufacturerInterface $manufacturer, $adapterName = '')
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
     * @return string
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
