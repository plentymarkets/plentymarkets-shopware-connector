<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Class TransferObjectInterface.
 */
interface TransferObjectInterface
{
    /**
     * @param array $params
     *
     * @return TransferObjectInterface
     */
    public static function fromArray(array $params = []);

    /**
     * return a uuid.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * return the unique type of the object.
     *
     * @return string
     */
    public function getType();
}
