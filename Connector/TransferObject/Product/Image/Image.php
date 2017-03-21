<?php

namespace PlentyConnector\Connector\TransferObject\Product\Image;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Image
 */
class Image extends AbstractValueObject
{
    /**
     * @var string
     */
    private $mediaIdentifier;

    /**
     * @var array
     */
    private $shopIdentifiers = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @return mixed
     */
    public function getMediaIdentifier()
    {
        return $this->mediaIdentifier;
    }

    /**
     * @param mixed $mediaIdentifier
     */
    public function setMediaIdentifier($mediaIdentifier)
    {
        $this->mediaIdentifier = $mediaIdentifier;
    }

    /**
     * @return array
     */
    public function getShopIdentifiers()
    {
        return $this->shopIdentifiers;
    }

    /**
     * @param array $shopIdentifiers
     */
    public function setShopIdentifiers($shopIdentifiers)
    {
        $this->shopIdentifiers = $shopIdentifiers;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
}
