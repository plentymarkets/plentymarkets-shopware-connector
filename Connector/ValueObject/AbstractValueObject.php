<?php

namespace SystemConnector\ValueObject;

use JsonSerializable;

abstract class AbstractValueObject implements ValueObjectInterface, JsonSerializable
{
    /**
     * @param array $params
     *
     * @return $this
     */
    public static function fromArray(array $params = []): ValueObjectInterface
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

    abstract public function getClassProperties();

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'class' => static::class,
            'properties' => $this->getClassProperties(),
        ];
    }
}
