<?php

namespace SystemConnector\Console\OutputHandler;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface OutputHandlerInterface
{
    /**
     * Initialize the handler. This needs to be done inside each console command
     */
    public function initialize(InputInterface $input, OutputInterface $output);

    /**
     * Start the progressbar
     *
     * @param int $count
     */
    public function startProgressBar($count);

    /**
     * Advance the progressbar one step
     */
    public function advanceProgressBar();

    /**
     * Finish the progressbar
     */
    public function finishProgressBar();

    /**
     * Write a new line
     *
     * @param string $messages
     */
    public function writeLine($messages = '');

    public function createTable(array $headers, array $rows);
}
