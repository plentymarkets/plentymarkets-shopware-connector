<?php

namespace ShopwareAdapter\Helper;

use PlentyConnector\Connector\TransferObject\AttributableInterface;

/**
 * Class AttributeHelperInterface
 */
interface AttributeHelperInterface
{
    /**
     * @param AttributableInterface $object
     * @param string                $fieldName
     */
    public function addFieldAsAttribute(AttributableInterface $object, $fieldName);
}
