<?php

namespace PlentymarketsAdapter\Client\Exception;

use Exception;

class LimitReachedException extends Exception
{
    /**
     * @var int
     */
    private $retryAfter;

    public function __construct($retryAfter)
    {
        $this->retryAfter = $retryAfter;

        parent::__construct();
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
