<?php

namespace PlentyConnector\Connector\ValueObject\Attribute;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface extends ValueObjectInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey();

    /**
     * {@inheritdoc}
     */
    public function getValue();
}
