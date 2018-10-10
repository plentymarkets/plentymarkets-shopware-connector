<?php

namespace SystemConnector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SystemConnector\BacklogService\BacklogService;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;

class BacklogInfoCommand extends Command
{
    /**
     * @var BacklogService
     */
    private $backlogService;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

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
