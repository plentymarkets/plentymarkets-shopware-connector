<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\Connector;
use PlentyConnector\Connector\Mapping\MappingServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchChangedManufacturerQuery;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Logger\ConsoleHandler;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually import manufacturer.
 */
class ImportManufacturerCommand extends ShopwareCommand
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * ImportManufacturerCommand constructor.
     *
     * @param Connector $connector
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:import:manufacturer');
        $this->setDescription('Import manufacturer');
        $this->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'If set, import every manufacturer'
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');

        /**
         * @var Logger
         */
        $logger = $this->container->get('plentyconnector.logger');
        $logger->pushHandler(new ConsoleHandler($output));

        /**
         * @var MappingServiceInterface $mappingService
         */
        $mappingService = Shopware()->Container()->get('plentyconnector.mapping_service');
        $mappingService->getMappingInformation();

        try {
            //$this->connector->handle(Manufacturer::getType(), 'All');
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
