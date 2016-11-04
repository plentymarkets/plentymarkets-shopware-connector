<?php

namespace PlentyConnector\Connector\TransferObject\OrderStatus;

/**
 * Class OrderStatus
 */
class OrderStatus implements OrderStatusInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return 'OrderStatus';
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self();
    }
}
