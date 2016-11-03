<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Class TransferObjectType.
 */
final class TransferObjectType
{
    const DEFINITION = 'Definition';
    const IDENTITY = 'Identity';
    const MANUFACTURER = 'Manufacturer';
    const MAPPING = 'Mapping';
    const PAYMENT_METHOD = 'PaymentMethod';
    const SHIPPING_PROFILE = 'ShippingProfile';

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
