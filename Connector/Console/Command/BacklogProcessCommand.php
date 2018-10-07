<?php

namespace PlentyConnector\Connector\Console\Command;

use Exception;
use PlentyConnector\Connector\BacklogService\BacklogService;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class BacklogProcessCommand extends Command
{
    /**
     * @var ServiceBusInterface
     */
    private $serviceBus;

    /**
     * @var BacklogService
     */
    private $backlogService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    public function __construct(
        ServiceBusInterface $serviceBus,
        BacklogService $backlogService,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->serviceBus = $serviceBus;
        $this->backlogService = $backlogService;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:backlog:process');
        $this->setDescription('process command backlog');
        $this->addArgument(
            'amount',
            InputArgument::OPTIONAL,
            'Amount of backlog elements to be processed',
            200
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (method_exists($this->logger, 'pushHandler')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

        $amount = (int) $input->getArgument('amount');

        $this->outputHandler->initialize($input, $output);
        $this->outputHandler->startProgressBar($amount);

        try {
            $counter = 0;

            while ($counter < $amount && $command = $this->backlogService->dequeue()) {
                ++$counter;

                $this->serviceBus->handle($command);
                $this->outputHandler->advanceProgressBar();
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        $this->outputHandler->finishProgressBar();
    }
}
