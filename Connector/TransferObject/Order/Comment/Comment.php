<?php

namespace PlentyConnector\Connector\TransferObject\Order\Comment;

/**
 * Class Comment
 */
class Comment
{
    const TYPE_INTERNAL = 1;
    const TYPE_CUSTOMER = 2;

    /**
     * @var int
     */
    private $type = self::TYPE_CUSTOMER;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}
