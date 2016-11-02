<?php

namespace PlentyConnector\Connector\ServiceBus\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Tactician\Middleware;
use Throwable;

/**
 * Wraps command execution inside a Doctrine ORM transaction
 */
class DoctrineMiddleware implements Middleware
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

    /**
     * @param object $command
     * @param callable $next
     *
     * @return mixed
     *
     * @throws Throwable
     * @throws Exception
     */
    public function execute($command, callable $next)
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $next($command);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        return $result;
    }
}
