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
    const MEDIA = 'Media';
    const ORDER_STATUS = 'OrderStatus';
    const PAYMENT_METHOD = 'PaymentMethod';
    const PAYMENT_STATUS = 'PaymentStatus';
    const PRODUCT = 'Product';
    const SHIPPING_PROFILE = 'ShippingProfile';
    const SHOP = 'Shop';
    const TRANSLATION = 'Translation';
    const UNIT = 'Unit';

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
