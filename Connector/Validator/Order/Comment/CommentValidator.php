<?php

namespace PlentyConnector\Connector\Validator\Order\Comment;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class CommentValidator
 */
class CommentValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
