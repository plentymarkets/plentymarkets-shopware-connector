<?php

namespace PlentyConnector\Connector\ValueObject;

abstract class AbstractValueObject implements ValueObjectInterface
{
    /**
     * @param array $params
     *
     * @return $this
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
