<?php

namespace SystemConnector\ValueObject;

interface ValueObjectInterface
{
    /**
     * @param array $params
     *
     * @return ValueObjectInterface
     */
    public static function fromArray(array $params = []);
}
