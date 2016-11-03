<?php

namespace PlentyConnector\Connector\QueryBus\Middleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Class QueryHandlerMiddleware.
 */
class QueryHandlerMiddleware implements Middleware
{
    /**
     * @var QueryHandlerInterface[]
     */
    private $handlers;

    /**
     * @param QueryHandlerInterface $handler
     */
    public function addHandler(QueryHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param QueryInterface $query
     * @param callable       $next
     *
     * @return mixed
     */
    public function execute($query, callable $next)
    {
        $handlers = array_filter($this->handlers, function (QueryHandlerInterface $handler) use ($query) {
            return $handler->supports($query);
        });

        if (0 === count($handlers)) {
            return $next($query);
        }

        $handler = array_shift($handlers);
        $response = $handler->handle($query);

        if (null !== $response) {
            return $response;
        }

        return $next($query);
    }
}
