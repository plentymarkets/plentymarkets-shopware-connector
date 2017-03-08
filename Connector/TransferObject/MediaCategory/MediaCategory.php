<?php

namespace PlentyConnector\Connector\TransferObject\MediaCategory;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class MediaCategory
 */
class MediaCategory extends AbstractTransferObject
{
    const TYPE = 'MediaCategory';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
