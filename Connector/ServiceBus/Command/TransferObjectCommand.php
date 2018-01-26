<?php

namespace PlentyConnector\Connector\ServiceBus\Command;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Class TransferObjectCommand
 */
class TransferObjectCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * @var string
     */
    private $objectType;

    /**
     * @var string
     */
    private $commandType;

    /**
     * @var TransferObjectInterface|string
     */
    private $payload;

    /**
     * TransferObjectCommand constructor.
     *
     * @param string                         $adapterName
     * @param string                         $objectType
     * @param string                         $commandType
     * @param TransferObjectInterface|string $payload
     */
    public function __construct($adapterName, $objectType, $commandType, $payload)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($commandType, CommandType::getAllTypes());

        if ($commandType === CommandType::HANDLE) {
            Assertion::isInstanceOf($payload, TransferObjectInterface::class);
        }

        if ($commandType === CommandType::REMOVE) {
            Assertion::uuid($payload);
        }

        $this->adapterName = $adapterName;
        $this->objectType = $objectType;
        $this->commandType = $commandType;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return string
     */
    public function getCommandType()
    {
        return $this->commandType;
    }

    /**
     * @return TransferObjectInterface|string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'adapterName' => $this->adapterName,
            'objectType' => $this->objectType,
            'commandType' => $this->commandType,
            'payload' => $this->payload,
        ];
    }
}
