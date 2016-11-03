<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Mapping
 */
class Mapping implements MappingInterface
{
    /**
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::MAPPING;
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        // TODO: Implement fromArray() method.
    }
}
