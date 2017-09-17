<?php

namespace PlentyConnector\Connector\BacklogService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use PlentyConnector\Connector\BacklogService\Command\HandleBacklogElementCommand;
use PlentyConnector\Connector\BacklogService\Model\Backlog;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

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
     * @var EntityRepository
     */
    private $repository;

    /**
     * BacklogService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Backlog::class);
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
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $backlog = $this->repository->findOneBy([], [
                'time' => 'ASC',
            ]);

            if (null === $backlog) {
                return null;
            }

            $this->entityManager->remove($backlog);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        } catch (Exception $exception) {
            $this->entityManager->getConnection()->rollBack();

            return null;
        }

        $this->entityManager->clear();

        $command = $backlog->getPayload();

        if ($command instanceof CommandInterface) {
            return new HandleBacklogElementCommand($command);
        }

        return null;
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
     * @return int
     */
    private function getEnqueuedAmount()
    {
        $query = 'SELECT count(id) FROM plenty_backlog';

        return (int) $this->entityManager->getConnection()->query($query)->fetchColumn();
    }
}
