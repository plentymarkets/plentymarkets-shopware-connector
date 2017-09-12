<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Commands\ShopwareCommand;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * CleanupCommand constructor.
     *
     * @param CleanupServiceInterface $cleanupService
     * @param LoggerInterface $logger
     * @param OutputHandlerInterface $outputHandler
     */
    public function __construct(
        CleanupServiceInterface $cleanupService,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->cleanupService = $cleanupService;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:cleanup');
        $this->setDescription('remove orphaned transfer objects');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (method_exists($this->logger, 'pushHandler')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

        $this->outputHandler->initialize($input, $output);

        try {
            $this->cleanupService->cleanup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        }
    }
}
