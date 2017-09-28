<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\BacklogService\Middleware\BacklogCommandHandlerMiddleware;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Command to manually process definitions.
 */
class ProcessCommand extends ShopwareCommand
{
    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * ProcessCommand constructor.
     *
     * @param ConnectorInterface     $connector
     * @param LoggerInterface        $logger
     * @param OutputHandlerInterface $outputHandler
     */
    public function __construct(
        ConnectorInterface $connector,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->connector = $connector;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;

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
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Exception $exception) {
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
