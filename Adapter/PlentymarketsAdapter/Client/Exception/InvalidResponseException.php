<?php

namespace PlentymarketsAdapter\Client\Exception;

use Exception;

class InvalidResponseException extends Exception
{
    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     */
    public static function fromParams($method, $path, $options): InvalidResponseException
    {
        $string = 'The response was null. Method: %s, Path: %s, options: %s';
        $message = sprintf($string, $method, $path, json_encode($options)) . "\n";

        return new static($message);
    }
}
