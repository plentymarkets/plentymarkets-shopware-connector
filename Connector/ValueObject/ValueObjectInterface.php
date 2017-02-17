<?php

namespace PlentyConnector\Connector\ValueObject;

/**
 * Interface ValueObjectInterface
 */
interface ValueObjectInterface
{
    /**
     * @param array $params
     */
    public static function fromArray(array $params = []);
}
