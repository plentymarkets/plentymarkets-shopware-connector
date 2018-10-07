<?php

namespace PlentyConnector\Connector\Console\Command;

use Exception;
use PlentyConnector\Connector\BacklogService\Middleware\BacklogCommandHandlerMiddleware;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\ServiceBus\QueryType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ProcessCommand extends Command
{
    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConnectorInterface $connector,
        OutputHandlerInterface $outputHandler,
        LoggerInterface $logger
    ) {
        $this->connector = $connector;
        $this->outputHandler = $outputHandler;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:process');
        $this->setDescription('process definitons');
        $this->setHelp($this->getHelpText());
        $this->addArgument(
            'objectType',
            InputArgument::OPTIONAL,
            'Object type to process. Leave empty for every object type'
        );
        $this->addArgument(
            'objectIdentifier',
            InputArgument::OPTIONAL,
            'Object Identifier to process. Leave empty for every object type'
        );
        $this->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'If set, ignore changes and process everything'
        );
        $this->addOption(
            'disableBacklog',
            null,
            InputOption::VALUE_NONE,
            'If set, commands will be handles directly'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = (bool) $input->getOption('all');

        if ((bool) $input->getOption('disableBacklog')) {
            BacklogCommandHandlerMiddleware::$active = false;
        }

        $objectType = $input->getArgument('objectType');
        $objectIdentifier = $input->getArgument('objectIdentifier');

        if (method_exists($this->logger, 'pushHandler')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

        $this->outputHandler->initialize($input, $output);

        try {
            if ($objectIdentifier) {
                $queryType = QueryType::ONE;

                BacklogCommandHandlerMiddleware::$active = false;
            } else {
                $queryType = $all ? QueryType::ALL : QueryType::CHANGED;
            }

            $this->connector->handle($queryType, $objectType, $objectIdentifier);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @return string
     */
    private function getHelpText()
    {
        $examples = [
            'import all products: plentyconnector:process Product --all',
            'import changed products: plentyconnector:process Product',
            'import single product: plentyconnector:process Product 753c7d5d-09be-4dd3-bd3f-3d5cc2e92dab',
            'import changed orders: plentyconnector:process Order',
        ];

        return "Examples:\n\n" . implode("\n", $examples);
    }
}
