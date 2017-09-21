<?php

namespace PlentyConnector\Connector\BacklogService;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use PDO;
use PlentyConnector\Connector\BacklogService\Command\HandleBacklogElementCommand;
use PlentyConnector\Connector\BacklogService\Model\Backlog;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BacklogService
 */
class BacklogService implements BacklogServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * BacklogService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->repository = $entityManager->getRepository(Backlog::class);
        $this->connection = $entityManager->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(CommandInterface $command)
    {
        $serializedCommand = serialize($command);
        $hash = md5($serializedCommand);

        if ($this->entryExists($hash)) {
            return;
        }

        $backlog = new Backlog();
        $backlog->setPayload($command);
        $backlog->setStatus(Backlog::STATUS_OPEN);
        $backlog->setHash($hash);

        $this->entityManager->persist($backlog);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            $selectQuery = 'SELECT * FROM plenty_backlog WHERE status = :status ORDER BY `time` ASC, `id` ASC LIMIT 1';
            $selectParams = [':status' => Backlog::STATUS_OPEN];
            $backlog = $this->connection->executeQuery($selectQuery, $selectParams)->fetch(PDO::FETCH_ASSOC);

            if ($backlog === false) {
                return null;
            }

            $updateQuery = 'UPDATE plenty_backlog SET status = :status WHERE id = :id';
            $affectedRows = $this->connection->executeUpdate($updateQuery, [
                ':id' => $backlog['id'],
                ':status' => Backlog::STATUS_PROCESSED,
            ]);

            if ($affectedRows !== 1) {
                return null;
            }

            $deleteQuery = 'DELETE FROM plenty_backlog WHERE id = :id';
            $affectedRows = $this->connection->executeUpdate($deleteQuery, [':id' => $backlog['id']]);

            if ($affectedRows !== 1) {
                return null;
            }

            $command = unserialize($backlog['payload']);

            if (!($command instanceof CommandInterface)) {
                return null;
            }

            return new HandleBacklogElementCommand($command);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        $amount = $this->getEnqueuedAmount();

        return [
            'amount_enqueued' => $amount,
        ];
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    private function entryExists($hash)
    {
        $backlog = $this->repository->findOneBy([
            'hash' => $hash,
        ]);

        if (!empty($backlog)) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    private function getEnqueuedAmount()
    {
        $query = 'SELECT count(id) FROM plenty_backlog';

        return (int) $this->connection->query($query)->fetchColumn();
    }
}
