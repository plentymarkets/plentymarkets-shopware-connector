<?php

namespace PlentyConnector\Connector\ServiceBus\Query;

/**
 * Class FetchOneQueryInterface
 */
interface FetchQueryInterface extends QueryInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getAdapterName();
}
