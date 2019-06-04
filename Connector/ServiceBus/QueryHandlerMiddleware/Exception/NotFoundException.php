<?php

namespace SystemConnector\ServiceBus\QueryHandlerMiddleware\Exception;

use Exception;
use SystemConnector\ServiceBus\Query\QueryInterface;

class NotFoundException extends Exception
{
    /**
     * @param QueryInterface $query
     *
     * @return self
     */
    public static function fromQuery(QueryInterface $query): self
    {
        $name = substr(strrchr(get_class($query), '\\'), 1);

        $message = 'No matching query handler found: ' . $name;

        return new self($message);
    }
}
