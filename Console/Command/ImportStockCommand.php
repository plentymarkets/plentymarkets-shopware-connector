<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\QueryBus\Query\GetRemoteStockUpdatesQuery;
use PlentyConnector\Logger\ConsoleHandler;
use PlentyConnector\Service\Stock\ImportStockServiceInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually import stock
 */
class ImportStockCommand extends ShopwareCommand
{
    /**
     * @var ImportStockServiceInterface
     */
    private $service;

    /**
     * ImportStockCommand constructor.
     *
     * @param ImportStockServiceInterface $service
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(ImportStockServiceInterface $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:import:stock');
        $this->setDescription('Import stock');
        $this->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'If set, import every stock'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Logger $logger
         */
        $logger = $this->container->get('plentyconnector.logger');
        $logger->pushHandler(new ConsoleHandler($output));

        $this->service->import();
    }
}
