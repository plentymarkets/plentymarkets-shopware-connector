<?php

namespace PlentymarketsAdapter\Client\Exception;

use Exception;

class LoginExpiredException extends Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}
