<?php

namespace PlentyConnector\Connector\ServiceBus\QueryHandlerMiddleware\Exception;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;

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

        $message = 'No matching query handler found: ' . $name;

        return new self($message);
    }
}
