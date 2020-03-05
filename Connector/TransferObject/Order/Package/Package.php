<?php

namespace SystemConnector\TransferObject\Order\Package;

use DateTimeImmutable;
use SystemConnector\ValueObject\AbstractValueObject;

class Package extends AbstractValueObject
{
    /**
     * @var DateTimeImmutable
     */
    private $shippingTime;

    /**
     * @var string
     */
    private $shippingCode = '';

    /**
     * @var null|string
     */
    private $shippingProvider;

    public function __construct()
    {
        $this->shippingTime = new DateTimeImmutable('now');
    }

    public function getShippingTime(): DateTimeImmutable
    {
        return $this->shippingTime;
    }

    /**
     * @param DateTimeImmutable $shippingTime
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
    }

    public function getShippingCode(): string
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

    /**
     * @return null|string
     */
    public function getShippingProvider()
    {
        return $this->shippingProvider;
    }

    /**
     * @param null|string $shippingProvider
     */
    public function setShippingProvider($shippingProvider = null)
    {
        $this->shippingProvider = $shippingProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'shippingTime' => $this->getShippingTime(),
            'shippingCode' => $this->getShippingCode(),
            'shippingProvider' => $this->getShippingProvider(),
        ];
    }
}
