<?php

namespace PlentyConnector\Connector\TransferObject\PaymentStatus;

/**
 * Class PaymentStatus
 */
class PaymentStatus implements PaymentStatusInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return 'PaymentStatus';
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
