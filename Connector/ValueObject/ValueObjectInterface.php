<?php

namespace SystemConnector\ValueObject;

interface ValueObjectInterface
{
    public static function fromArray(array $params = []): ValueObjectInterface;
}
