<?php

namespace PlentyConnector\Connector\QueryBus\Query;

/**
 * Class FetchOneQueryInterface
 */
interface FetchOneQueryInterface extends QueryInterface
{
    /**
     * @return string
     */
    public function getIdentifier();
}
