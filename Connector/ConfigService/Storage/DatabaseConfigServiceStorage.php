<?php

namespace SystemConnector\ConfigService\Storage;

use DateTimeInterface;
use Doctrine\DBAL\Connection;

class DatabaseConfigServiceStorage implements ConfigServiceStorageInterface
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     * @param string     $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'config');
        $queryBuilder->select([
            'config.name',
            'config.value',
        ]);

        $configElements = $queryBuilder->execute()->fetchAll();

        $result = [];

        foreach ($configElements as $element) {
            $result[$element['name']] = $element['value'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'config');
        $queryBuilder->select([
            'config.value',
        ]);
        $queryBuilder->andWhere('config.name = :name');
        $queryBuilder->setParameter(':name', $name);

        $configValue = $queryBuilder->execute()->fetchColumn();

        if (!$configValue) {
            return null;
        }

        return $configValue;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format(DATE_W3C);
        }

        $affectedRows = $this->connection->update(
            $this->table,
            [
                'value' => $value,
            ],
            [
                'name' => $name,
            ]
        );

        if (empty($affectedRows)) {
            $this->connection->insert($this->table, [
                'name' => $name,
                'value' => $value,
            ]);
        }
    }
}
