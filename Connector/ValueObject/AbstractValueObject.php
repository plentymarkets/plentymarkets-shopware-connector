<?php

namespace PlentyConnector\Connector\ValueObject;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class AbstractValueObject
 */
abstract class AbstractValueObject implements ValueObjectInterface
{
    /**
     * @param array $params
     *
     * @return ValueObjectInterface
     */
    public static function fromArray(array $params = [])
    {
        $object = new static();

        foreach ($params as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($object, $method)) {
                $object->$method($value);
            }
        }

        return $object;
    }
}
