<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually process definitions.
 */
class CleanupCommand extends ShopwareCommand
{
    /**
     * @var CleanupServiceInterface
     */
    private $cleanupService;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * CleanupCommand constructor.
     *
     * @param CleanupServiceInterface $cleanupService
     * @param Logger                  $logger
     *
     * @throws LogicException
     */
    public function __construct(CleanupServiceInterface $cleanupService, Logger $logger)
    {
        $this->cleanupService = $cleanupService;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:cleanup');
        $this->setDescription('cleanup task');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->pushHandler(new ConsoleHandler($output));

        try {
            $this->cleanupService->cleanup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        }
    }
}
