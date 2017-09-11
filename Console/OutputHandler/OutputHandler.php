<?php

namespace PlentyConnector\Console\OutputHandler;

use Assert\Assertion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class OutputHandler
 */
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->style = new SymfonyStyle($input, $output);
    }

    /**
     * @param int $count
     */
    public function startProgressBar($count)
    {
        if (null === $this->output) {
            return;
        }

        Assertion::integer($count);
        Assertion::greaterThan($count, 0);

        $this->style->progressStart($count);
    }

    public function advanceProgressBar()
    {
        if (null === $this->output) {
            return;
        }

        $this->style->progressAdvance();
    }

    public function finishProgressBar()
    {
        if (null === $this->output) {
            return;
        }

        $this->style->progressFinish();
    }

    /**
     * @param string $messages
     */
    public function writeLine($messages)
    {
        if (null === $this->output) {
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
        if (null === $this->output) {
            return;
        }

        $this->style->table($headers, $rows);
    }
}
