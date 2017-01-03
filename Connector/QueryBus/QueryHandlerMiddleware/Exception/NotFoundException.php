<?php

namespace PlentyConnector\Connector\QueryBus\QueryHandlerMiddleware\Exception;

use Exception;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Class NotFoundException.
 */
class NotFoundException extends Exception
{
    /**
     * @param QueryInterface $query
     *
     * @return self
     */
    public static function fromQuery(QueryInterface $query)
    {
        $name = substr(strrchr(get_class($query), '\\'), 1);

        $message = 'No handler was found for: ' . $name;

        return new self($message);
    }
}
