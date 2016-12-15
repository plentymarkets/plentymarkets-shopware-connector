<?php


namespace PlentymarketsAdapter\Client\Handler;

/**
 * Class HandlerFactory
 */
class HandlerFactory
{
    /**
     * @return callable
     */
    public static function factory()
    {
        return \GuzzleHttp\choose_handler();
    }
}
