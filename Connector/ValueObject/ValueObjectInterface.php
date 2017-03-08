<?php

namespace PlentyConnector\Connector\ValueObject;

/**
 * Interface ValueObjectInterface
 */
interface ValueObjectInterface
{
    /**
     * @param array $params
     *
     * @return ValueObjectInterface
     */
    public static function fromArray(array $params = []);
}
