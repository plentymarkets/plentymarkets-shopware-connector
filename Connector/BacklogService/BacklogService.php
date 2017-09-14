<?php

namespace PlentyConnector\Connector\BacklogService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        $hash = sha1(serialize($command));

        if ($this->entryExists($hash)) {
            return;
        }

        $backlog = new Backlog();
        $backlog->setPayload($command);
        $backlog->setHash($hash);

        $this->entityManager->persist($backlog);
        $this->entityManager->flush();
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    private function entryExists($hash) {
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
    public function dequeue()
    {
        $backlog = $this->repository->findOneBy([], [
            'time' => 'ASC'
        ]);

        if (null === $backlog) {
            return null;
        }

        try {
            $this->entityManager->remove($backlog);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            // fail silently
        }

        $command = $backlog->getPayload();

        if ($command instanceof CommandInterface) {
            return new HandleBacklogElementCommand($command);
        }

        return null;
    }
}
