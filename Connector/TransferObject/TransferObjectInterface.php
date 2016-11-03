<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Class TransferObjectInterface.
 */
interface TransferObjectInterface
{
    /**
     * @return string
     */
    public static function getType();

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = []);
}
