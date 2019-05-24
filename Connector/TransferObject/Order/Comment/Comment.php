<?php

namespace SystemConnector\TransferObject\Order\Comment;

use ReflectionClass;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Attribute\Attribute;

class Comment extends AbstractValueObject implements AttributableInterface
{
    const TYPE_INTERNAL = 'internal';
    const TYPE_CUSTOMER = 'customer';

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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getComment(): string
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
    public function getAttributes() :array
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
            'comment' => $this->getComment(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
