<?php

namespace PlentyConnector\Connector\Exception;

use InvalidArgumentException;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;

/**
 * Class MissingQueryException
 */
class MissingQueryException extends InvalidArgumentException
{
    /**
     * @param DefinitionInterface $definition
     *
     * @return self
     */
    public static function fromDefinition(DefinitionInterface $definition)
    {
        $message = sprintf('No Query could be generated for the current definition: %s', $definition) . "\n";

        return new static($message);
    }
}
