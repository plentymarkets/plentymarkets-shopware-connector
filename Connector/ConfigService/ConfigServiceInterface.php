<?php

namespace SystemConnector\ConfigService;

interface ConfigServiceInterface
{
    /**
     * Returns all config elements as array where the key is the element name.
     */
    public function getAll(): array;

    /**
     * Returns the given config element for that key. Default if it doesnt exist.
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
