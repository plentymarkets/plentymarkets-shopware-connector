<?php

namespace PlentyConnector\Connector\Exception;

use InvalidArgumentException;
use PlentyConnector\Connector\TransferObject\Definition\DefinitionInterface;

/**
 * Class MissingCommandException
 */
class MissingCommandException extends InvalidArgumentException
{
    /**
     * @param DefinitionInterface $definition
     *
     * @return self
     */
    public static function fromDefinition(DefinitionInterface $definition)
    {
        $message = sprintf('No command could be generated for the current definition: %s', $definition) . "\n";

        return new static($message);
    }
}
