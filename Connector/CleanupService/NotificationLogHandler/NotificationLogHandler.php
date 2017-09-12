<?php

namespace PlentyConnector\Connector\CleanupService\NotificationLogHandler;

use Closure;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class NotificationLogHandler
 */
class NotificationLogHandler extends AbstractProcessingHandler
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

    /**
     * NotificationLogHandler constructor.
     *
     * @param Closure $handler
     */
    public function __construct($handler)
    {
        $this->handler = $handler;

        parent::__construct(Logger::ERROR);
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
        call_user_func($this->handler);
        $this->isProcessing = false;
    }
}
