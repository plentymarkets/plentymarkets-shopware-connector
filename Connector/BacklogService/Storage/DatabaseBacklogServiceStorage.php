<?php

namespace SystemConnector\BacklogService\Storage;

use DateTime;
use Doctrine\DBAL\Connection;
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
        $selectQuery = 'SELECT * FROM :table WHERE `status` = :status ORDER BY `priority` DESC, `id` ASC LIMIT 1';

        $backlog = $this->connection->fetchAssoc($selectQuery, [
            ':table' => $this->table,
            ':status' => BacklogService::STATUS_OPEN,
        ]);

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
            ':id' => $backlog['id'],
        ]);

        if ($affectedRows !== 1) {
            return null;
        }

        $command = unserialize($backlog['payload']);

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
        $query = 'SELECT id FROM :table WHERE hash = :hash';

        $backlog = $this->connection->fetchAssoc($query, [
            ':table' => $this->table,
            ':hash' => $hash,
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
        $query = 'SELECT count(id) FROM :table';

        return (int) $this->connection->fetchColumn($query, [
            ':table' => $this->table,
        ]);
    }
}
