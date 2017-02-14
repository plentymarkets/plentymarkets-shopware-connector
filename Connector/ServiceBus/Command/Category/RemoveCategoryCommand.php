<?php

namespace PlentyConnector\Connector\ServiceBus\Command\Category;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Command\RemoveCommandInterface;

/**
 * Class RemoveCategoryCommand.
 */
class RemoveCategoryCommand implements RemoveCommandInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * @var string
     */
    private $objectIdentifier;

    /**
     * RemoveCategoryCommand constructor.
     *
     * @param string $adapterName the classname of the target adapter
     * @param string $objectIdentifier the identifier of the transferobject which will be handeled
     */
    public function __construct($adapterName, $objectIdentifier)
    {
        Assertion::string($adapterName);
        Assertion::uuid($objectIdentifier);

        $this->adapterName = $adapterName;
        $this->objectIdentifier = $objectIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
            'objectIdentifier' => $this->objectIdentifier,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->adapterName = $payload['adapterName'];
        $this->objectIdentifier = $payload['objectIdentifier'];
    }
}
