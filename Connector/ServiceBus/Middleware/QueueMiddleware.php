<?php

namespace PlentyConnector\Connector\ServiceBus\Middleware;

/**
 * Class QueueMiddleware
 *
 * @package PlentyConnector\Connector\ServiceBus\Middleware
 */
class QueueMiddleware implements Middleware
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


}
