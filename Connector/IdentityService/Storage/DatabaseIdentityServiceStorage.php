<?php

namespace SystemConnector\IdentityService\Storage;

use Doctrine\DBAL\Connection;
use SystemConnector\ValueObject\Identity\Identity;

class DatabaseIdentityServiceStorage implements IdentityServiceStorageInterface
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
    public function findOneBy(array $criteria = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'identity');
        $queryBuilder->select([
            'identity.objectIdentifier',
            'identity.objectType',
            'identity.adapterIdentifier',
            'identity.adapterName',
        ]);
        $queryBuilder->setMaxResults(1);

        foreach ($criteria as $key => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($key, ':' . $key));
            $queryBuilder->setParameter(':' . $key, $value);
        }

        $result = $queryBuilder->execute()->fetch();

        if (!empty($result)) {
            return Identity::fromArray([
                'objectIdentifier' => $result['objectIdentifier'],
                'objectType' => $result['objectType'],
                'adapterIdentifier' => $result['adapterIdentifier'],
                'adapterName' => $result['adapterName'],
            ]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->table, 'identity');
        $queryBuilder->select([
            'identity.objectIdentifier',
            'identity.objectType',
            'identity.adapterIdentifier',
            'identity.adapterName',
        ]);

        foreach ($criteria as $key => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($key, ':' . $key));
            $queryBuilder->setParameter(':' . $key, $value);
        }

        $result = $queryBuilder->execute()->fetchAll();

        return array_map(function (array $result) {
            return Identity::fromArray([
                'objectIdentifier' => $result['objectIdentifier'],
                'objectType' => $result['objectType'],
                'adapterIdentifier' => $result['adapterIdentifier'],
                'adapterName' => $result['adapterName'],
            ]);
        }, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Identity $identity)
    {
        $this->connection->insert($this->table, [
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Identity $identity, array $data = [])
    {
        $this->connection->update($this->table, $data, [
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $this->connection->delete($this->table, [
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
        ]);
    }
}
