<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Mapping
 */
class Mapping implements MappingInterface
{
    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * @var TransferObjectInterface[]
     */
    private $originTransferObjects;

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * The TransferObject class name.
     *
     * @var string
     */
    private $objectType;

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
