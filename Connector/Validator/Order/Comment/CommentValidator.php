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
        Assertion::inArray($object->getType(), $object->getTypes());
        Assertion::string($object->getComment());
        Assertion::notBlank($object->getComment());
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
