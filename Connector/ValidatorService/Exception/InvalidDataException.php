<?php

namespace SystemConnector\ValidatorService\Exception;

use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValueObject\ValueObjectInterface;

class InvalidDataException extends \Exception
{
    /**
     * @param TransferObjectInterface|ValueObjectInterface $object
     * @param string                                       $message
     * @param string                                       $propertyPath
     * @param array                                        $parents
     *
     * @return InvalidDataException
     */
    public static function fromObject($object, $message, $propertyPath, array $parents = [])
    {
        $newMessage = '';

        foreach ($parents as $parent) {
            if ($parent instanceof ValueObjectInterface) {
                $newMessage .= get_class($parent) . ' ';
            }

            if ($parent instanceof TransferObjectInterface) {
                $newMessage .= get_class($parent) . ' ObjectIdentifier: ' . $parent->getIdentifier();
            }
        }

        if ($object instanceof ValueObjectInterface) {
            $newMessage .= ' ' . $message . ' Path: ' . $propertyPath;
        }

        if ($object instanceof TransferObjectInterface) {
            $newMessage .= ' ObjectIdentifier: ' . $object->getIdentifier() . ' Message: ' . $message . ' Path: ' . $propertyPath;
        }

        $newMessage = trim($newMessage);

        return new self($newMessage);
    }
}
