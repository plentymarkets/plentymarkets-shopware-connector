<?php

namespace SystemConnector\ServiceBus\QueryHandlerMiddleware;

use League\Tactician\Middleware;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryHandlerMiddleware\Exception\NotFoundException;

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
