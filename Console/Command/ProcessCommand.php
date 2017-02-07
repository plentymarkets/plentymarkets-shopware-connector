<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\Connector;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\ServiceBus\QueryType;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually process definitions.
 */
class ProcessCommand extends ShopwareCommand
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ProcessCommand constructor.
     *
     * @param Connector $connector
     * @param Logger    $logger
     *
     * @throws LogicException
     */
    public function __construct(Connector $connector, Logger $logger)
    {
        $this->connector = $connector;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:process');
        $this->setDescription('process all definitions according');
        $this->addArgument(
            'objectType',
            InputArgument::OPTIONAL,
            'Object type to process. Leave empty for every object type'
        );
        $this->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'If set, ignore changes and process everything'
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = (bool) $input->getOption('all');
        $objectType = $input->getArgument('objectType');

        $this->logger->pushHandler(new ConsoleHandler($output));

        try {
            $queryType = $all ? QueryType::ALL : QueryType::CHANGED;

            $this->connector->handle($queryType, $objectType);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
