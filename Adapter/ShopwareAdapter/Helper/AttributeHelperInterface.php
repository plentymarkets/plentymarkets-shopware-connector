<?php

namespace ShopwareAdapter\Helper;

use SystemConnector\TransferObject\AttributableInterface;

interface AttributeHelperInterface
{
    /**
     * @param AttributableInterface $object
     * @param string                $fieldName
     */
    public function addFieldAsAttribute(AttributableInterface $object, $fieldName);
}
