<?php

namespace SystemConnector\ConfigService\Storage;

interface ConfigServiceStorageInterface
{
    /**
     * Returns all config elements as array where the key is the element name.
     *
     * @return array
     */
    public function getAll();

    /**
     * Returns the given config element for that key. Default if it doenst exist.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Sets the config key to the given value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);
}
