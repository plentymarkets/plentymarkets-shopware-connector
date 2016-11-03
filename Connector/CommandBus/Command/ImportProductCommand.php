<?php

namespace PlentyConnector\Connector\CommandBus\Command;

use PlentyConnector\Connector\TransferObject\Product;

/**
 * Class ImportProductCommand.
 */
class ImportProductCommand implements CommandInterface
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $adapterName;

    /**
     * ImportLocalManufacturerCommand constructor.
     *
     * @param Product $product     the transferobject which will be handeled
     * @param string  $adapterName the classname of the target adapter
     */
    public function __construct(Product $product, $adapterName = '')
    {
        $this->product = $product;
        $this->adapterName = $adapterName;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
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
            'product' => $this->product,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->adapterName = $payload['adapterName'];
        $this->product = $payload['product'];
    }
}
