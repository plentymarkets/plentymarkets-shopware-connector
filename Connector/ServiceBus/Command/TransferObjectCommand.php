<?php

namespace SystemConnector\ServiceBus\Command;

use Assert\Assertion;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\TransferObjectInterface;

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
     * @var int
     */
    private $priority;

    /**
     * @var string|TransferObjectInterface
     */
    private $payload;

    /**
     * @param string                         $adapterName
     * @param string                         $objectType
     * @param string                         $commandType
     * @param int                            $priority
     * @param string|TransferObjectInterface $payload
     */
    public function __construct($adapterName, $objectType, $commandType, $priority, $payload)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($commandType, CommandType::getAllTypes());
        Assertion::integer($priority);

        if ($commandType === CommandType::HANDLE) {
            Assertion::isInstanceOf($payload, TransferObjectInterface::class);
        }

        if ($commandType === CommandType::REMOVE) {
            Assertion::uuid($payload);
        }

        $this->adapterName = $adapterName;
        $this->objectType = $objectType;
        $this->commandType = $commandType;
        $this->priority = $priority;
        $this->payload = $payload;
    }

    public function getAdapterName(): string
    {
        return $this->adapterName;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function getCommandType(): string
    {
        return $this->commandType;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'adapterName' => $this->adapterName,
            'objectType' => $this->objectType,
            'commandType' => $this->commandType,
            'priority' => $this->priority,
            'payload' => $this->payload,
        ];
    }
}
