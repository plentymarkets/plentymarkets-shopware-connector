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
     * @param callable $next
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

        $handlers = array_filter($this->handlers, function (QueryHandlerInterface $handler) use ($query) {
            if (!($query instanceof QueryInterface)) {
                return false;
            }

            return $handler->supports($query);
        });

        if (0 === count($handlers)) {
            if ($query instanceof QueryInterface) {
                throw NotFoundException::fromQuery($query);
            }

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
