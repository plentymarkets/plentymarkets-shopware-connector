<?php

namespace PlentyConnector\Console\Command;

use PlentyConnector\Connector\BacklogService\BacklogService;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually process definitions.
 */
class BacklogInfoCommand extends ShopwareCommand
{
    /**
     * @var BacklogService
     */
    private $backlogService;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * BacklogInfoCommand constructor.
     *
     * @param BacklogService         $backlogService
     * @param OutputHandlerInterface $outputHandler
     */
    public function __construct(
        BacklogService $backlogService,
        OutputHandlerInterface $outputHandler
    ) {
        $this->backlogService = $backlogService;
        $this->outputHandler = $outputHandler;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:backlog:info');
        $this->setDescription('displays information about the backlog');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHandler->initialize($input, $output);

        $info = $this->backlogService->getInfo();

        $this->outputHandler->createTable(['info', 'value'], [
            ['amount enqueued', $info['amount_enqueued']],
        ]);
    }
}
