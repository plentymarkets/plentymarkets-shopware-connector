<?php

namespace SystemConnector\BacklogService\Storage;

use DateTime;
use Doctrine\DBAL\Connection;
use PDO;
use SystemConnector\BacklogService\BacklogService;
use SystemConnector\BacklogService\Command\HandleBacklogElementCommand;
use SystemConnector\ServiceBus\Command\CommandInterface;

class DatabaseBacklogServiceStorage implements BacklogServiceStorageInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    public function __construct(
        Connection $connection,
        $table
    ) {
        $this->connection = $connection;
        $this->table = $table;
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

        $this->connection->insert($this->table, [
            'payload' => $serializedCommand,
            'hash' => $hash,
            'priority' => $command->getPriority(),
            'time' => (new DateTime('now'))->format(DATE_W3C),
            'status' => BacklogService::STATUS_OPEN,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'backlog');
        $queryBuilder->andWhere('backlog.status = :status');
        $queryBuilder->setParameter(':status', BacklogService::STATUS_OPEN);
        $queryBuilder->addOrderBy('priority', 'DESC');
        $queryBuilder->addOrderBy('id', 'ASC');
        $queryBuilder->setMaxResults(1);
        $queryBuilder->select('*');

        $backlog = $queryBuilder->execute()->fetch(PDO::FETCH_ASSOC);

        if (empty($backlog)) {
            return null;
        }

        $affectedRows = $this->connection->update(
            $this->table,
            [
                'status' => BacklogService::STATUS_PROCESSED,
            ],
            [
                'id' => $backlog['id'],
            ]
        );

        if ($affectedRows !== 1) {
            return null;
        }

        $affectedRows = $this->connection->delete($this->table, [
            'id' => $backlog['id'],
        ]);

        if ($affectedRows !== 1) {
            return null;
        }

        $command = unserialize($backlog['payload'], [
            'allowed_classes' => [
                CommandInterface::class,
            ],
        ]);

        if (!($command instanceof CommandInterface)) {
            return null;
        }

        return new HandleBacklogElementCommand($command);
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
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'backlog');
        $queryBuilder->andWhere('backlog.hash = :hash');
        $queryBuilder->setParameter(':hash', $hash);
        $queryBuilder->setMaxResults(1);
        $queryBuilder->select('backlog.id');

        $backlog = $queryBuilder->execute()->fetch(PDO::FETCH_ASSOC);

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
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'backlog');
        $queryBuilder->select('count(backlog.id) as count');

        return $queryBuilder->execute()->fetchColumn();
    }
}
