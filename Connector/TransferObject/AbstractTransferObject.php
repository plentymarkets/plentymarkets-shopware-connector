<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Class AbstractTransferObject
 */
abstract class AbstractTransferObject implements TransferObjectInterface
{
    /**
     * @param array $params
     *
     * @return TransferObjectInterface
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
