<?php

namespace SystemConnector\DefinitionProvider\Struct;

class Definition
{
    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName = '';

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName = '';

    /**
     * The TransferObject class name.
     *
     * @var string
     */
    private $objectType = '';

    /**
     * Definition priority. Higher priority means earlier processing of the definition and transfer objects
     *
     * @var int
     */
    private $priority = 0;

    /**
     * active/deactivate flag. Can be used to decorate definitions and disable them completely
     *
     * @var bool
     */
    private $active = true;

    /**
     * @return string
     */
    public function getOriginAdapterName(): string
    {
        return $this->originAdapterName;
    }

    /**
     * @param string $originAdapterName
     */
    public function setOriginAdapterName($originAdapterName)
    {
        $this->originAdapterName = $originAdapterName;
    }

    /**
     * @return string
     */
    public function getDestinationAdapterName(): string
    {
        return $this->destinationAdapterName;
    }

    /**
     * @param string $destinationAdapterName
     */
    public function setDestinationAdapterName($destinationAdapterName)
    {
        $this->destinationAdapterName = $destinationAdapterName;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param null|int $priority
     */
    public function setPriority($priority = null)
    {
        if (null === $priority) {
            $priority = 0;
        }

        $this->priority = $priority;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
}
