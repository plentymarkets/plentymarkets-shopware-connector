<?php

namespace PlentyConnector\Connector\ConfigService;

/**
 * Interface ConfigServiceInterface.
 */
interface ConfigServiceInterface
{
    /**
     * Returns the given config element for that key. Default if it doenst exist.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Sets the config key to the given value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);
}
