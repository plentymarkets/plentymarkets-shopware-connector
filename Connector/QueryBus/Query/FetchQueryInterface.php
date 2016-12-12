<?php

namespace PlentyConnector\Connector\QueryBus\Query;

/**
 * Class FetchOneQueryInterface
 */
interface FetchQueryInterface extends QueryInterface
{
    /**
     * @return string
     */
    public function getIdentifier();
}
