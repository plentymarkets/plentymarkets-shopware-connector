<?php

namespace SystemConnector\Validator\Order\Comment;

use Assert\Assertion;
use SystemConnector\TransferObject\Order\Comment\Comment;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class CommentValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Comment;
    }

    /**
     * @param Comment $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getTypes(), null, 'order.comment.type');
        Assertion::string($object->getComment(), null, 'order.comment.comment');
        Assertion::notBlank($object->getComment(), null, 'order.comment.comment');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'order.comment.attributes');
    }
}
