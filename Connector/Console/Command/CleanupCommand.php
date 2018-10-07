<?php

namespace PlentyConnector\Connector\Console\Command;

use Exception;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CleanupCommand extends Command
{
    /**
     * @var CleanupServiceInterface
     */
    private $cleanupService;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CleanupServiceInterface $cleanupService,
        OutputHandlerInterface $outputHandler,
        LoggerInterface $logger
    ) {
        $this->cleanupService = $cleanupService;
        $this->outputHandler = $outputHandler;
        $this->logger = $logger;

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
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
