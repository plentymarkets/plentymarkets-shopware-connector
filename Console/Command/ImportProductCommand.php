<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Logger\ConsoleHandler;
use PlentyConnector\Service\Product\ImportProductService;
use PlentyConnector\Service\Product\ImportProductServie;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually import products
 */
class ImportProductCommand extends ShopwareCommand
{
    /**
     * @var ImportProductService
     */
    private $service;

    /**
     * ImportProductCommand constructor.
     *
     * @param ImportProductService $service
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(ImportProductService $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:import:product');
        $this->setDescription('Import Products');
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

        try {
            $this->service->import();
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
