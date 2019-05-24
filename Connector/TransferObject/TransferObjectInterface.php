<?php

namespace SystemConnector\TransferObject;

interface TransferObjectInterface
{
    /**
     * @param array $params
     *
     * @return TransferObjectInterface
     */
    public static function fromArray(array $params = []) :TransferObjectInterface;

    /**
     * return a uuid.
     *
     * @return string
     */
    public function getIdentifier() :string;

    /**
     * set a uuid.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier);

    /**
     * return the unique type of the object.
     *
     * @return string
     */
    public function getType() :string;
}
