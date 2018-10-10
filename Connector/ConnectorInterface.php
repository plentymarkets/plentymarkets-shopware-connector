<?php

namespace SystemConnector;

interface ConnectorInterface
{
    /**
     * @param int         $queryType
     * @param null|string $objectType
     * @param null|string $identifier
     */
    public function handle($queryType, $objectType = null, $identifier = null);
}
