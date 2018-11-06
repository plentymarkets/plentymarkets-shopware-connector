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

    /**
     * @return int
     */
    public function getRetryAfter()
    {
        return $this->retryAfter;
    }
}
