<?php

namespace PlentyConnector\Console\Command;

use Exception;
use Monolog\Logger;
use PlentyConnector\Logger\ConsoleHandler;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProcessCommandStackCommand
 *
 * @package PlentyConnector\Console\Command
 */
class ProcessBacklogCommand extends ShopwareCommand
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:process');
        $this->setDescription('process command stack');
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

        return true;
    }
}
