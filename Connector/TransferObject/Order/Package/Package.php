<?php

namespace PlentyConnector\Connector\TransferObject\Order\Package;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Package
 */
class Package extends AbstractValueObject
{
    /**
     * @var \DateTimeImmutable
     */
    private $shippingTime;

    /**
     * @var string
     */
    private $shippingCode;

    /**
     * Package constructor.
     */
    public function __construct()
    {
        $timezone = new \DateTimeZone('UTC');

        $this->shippingTime = new \DateTimeImmutable('now', $timezone);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param \DateTimeImmutable $shippingTime
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
    }

    /**
     * @return string
     */
    public function getShippingCode()
    {
        return $this->shippingCode;
    }

    /**
     * @param string $shippingCode
     */
    public function setShippingCode($shippingCode)
    {
        $this->shippingCode = $shippingCode;
    }
}
