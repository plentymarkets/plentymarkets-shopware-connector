<?php

namespace SystemConnector\Console\OutputHandler;

use Assert\Assertion;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OutputHandler implements OutputHandlerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var SymfonyStyle
     */
    private $style;

    /**
     * @var int
     */
    private $verbosity;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->verbosity = $output->getVerbosity();
        $this->style = new SymfonyStyle($input, $output);
    }

    /**
     * @param int $count
     */
    public function startProgressBar($count)
    {
        if (!$this->isEnabled()) {
            return;
        }

        Assertion::integer($count);

        $this->progressBar = $this->style->createProgressBar($count);
        $this->progressBar->setFormat('debug');
        $this->progressBar->start();
    }

    public function advanceProgressBar()
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (null === $this->progressBar) {
            return;
        }

        $this->progressBar->advance();
    }

    public function finishProgressBar()
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (null === $this->progressBar) {
            return;
        }

        $this->progressBar->finish();
        $this->style->newLine(2);
    }

    /**
     * @param string $messages
     */
    public function writeLine($messages = '')
    {
        if (!$this->isEnabled()) {
            return;
        }

        Assertion::string($messages);

        $this->style->writeln($messages);
    }

    /**
     * @param array $headers
     * @param array $rows
     */
    public function createTable(array $headers, array $rows)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->style->table($headers, $rows);
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        if (null === $this->output) {
            return false;
        }

        if ($this->verbosity > OutputInterface::VERBOSITY_NORMAL) {
            return false;
        }

        return true;
    }
}
