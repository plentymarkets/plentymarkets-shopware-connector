<?php

namespace SystemConnector\TransferObject;

interface TransferObjectInterface
{
    public static function fromArray(array $params = []): TransferObjectInterface;

    /**
     * return a uuid.
     */
    public function getIdentifier(): string;

    /**
     * set a uuid.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier);

    /**
     * return the unique type of the object.
     */
    public function getType(): string;
}
