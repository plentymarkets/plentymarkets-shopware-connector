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
    public function getType();

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = []);
}
