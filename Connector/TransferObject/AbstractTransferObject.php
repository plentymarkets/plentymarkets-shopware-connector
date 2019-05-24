<?php

namespace SystemConnector\TransferObject;

use JsonSerializable;

abstract class AbstractTransferObject implements TransferObjectInterface, JsonSerializable
{
    /**
     * @param array $params
     *
     * @return $this
     */
    public static function fromArray(array $params = []) :TransferObjectInterface
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
