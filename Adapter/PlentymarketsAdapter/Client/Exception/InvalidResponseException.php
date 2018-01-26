<?php

namespace PlentymarketsAdapter\Client\Exception;

use Exception;

/**
 * Class InvalidResponseException
 */
class InvalidResponseException extends Exception
{
    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return InvalidResponseException
     */
    public static function fromParams($method, $path, $options)
    {
        $string  = 'The response was null. Method: %s, Path: %s, options: %s';
        $message = sprintf($string, $method, $path, json_encode($options)) . "\n";

        return new static($message);
    }
}
