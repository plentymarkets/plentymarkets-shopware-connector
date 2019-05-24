<?php

namespace SystemConnector\ConfigService\Storage;

interface ConfigServiceStorageInterface
{
    /**
     * Returns all config elements as array where the key is the element name.
     *
     * @return array
     */
    public function getAll() :array;

    /**
     * Returns the given config element for that key. Default if it doesnt exist.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function get($name);

    /**
     * Sets the config key to the given value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);
}
