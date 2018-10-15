<?php

namespace SystemConnector\CleanupService\CallbackLogHandler;

use Closure;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class CallbackLogHandler extends AbstractProcessingHandler
{
    /**
     * The clousure which will be called if an error message was logged
     *
     * @var Closure
     */
    private $handler;

    /**
     * enable logging inside the closure
     *
     * @var bool
     */
    private $isProcessing = false;

    public function __construct($handler, $level = Logger::ERROR)
    {
        $this->handler = $handler;

        parent::__construct($level);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;
        call_user_func($this->handler, $record);
        $this->isProcessing = false;
    }
}
