<?php

namespace PlentyConnector\Console\Command;

use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentymarketsAdapter\Client\Client;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand
 */
class TestCommand extends ShopwareCommand
{
    /**
     * @var Client
     */
    private $client;

    /**
     * HandleManufacturerCommand constructor.
     *
     * @param Client $client
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:test');
        $this->setDescription('test');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return null|int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Logger
         */
        $logger = $this->container->get('plenty_connector.logger');
        $logger->pushHandler(new ConsoleHandler($output));

        $mapping = $this->container->get('plenty_connector.mapping_service');
        $mappings = $mapping->getMappingInformation();

        $output->writeln('mapping:' .  count($mappings));

        try {
            //$this->connector->handle(Manufacturer::getType(), 'All');
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
