<?php

namespace PlentyConnector\Connector\ServiceBus\QueryHandlerMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandlerMiddleware\Exception\NotFoundException;

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
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function execute($query, callable $next)
    {
        if (null === $this->handlers) {
            return $next($query);
        }

        if (!($query instanceof QueryInterface)) {
            return $next($query);
        }

        $handlers = array_filter($this->handlers, function (QueryHandlerInterface $handler) use ($query) {
            return $handler->supports($query);
        });

        if (0 === count($handlers)) {
            throw NotFoundException::fromQuery($query);
        }

        /**
         * @var QueryHandlerInterface $handler
         */
        $handler = array_shift($handlers);
        $response = $handler->handle($query);

        if (null !== $response) {
            return $response;
        }

        return $next($query);
    }
}
