<?php

namespace PlentyConnector\Connector\MappingService;

use PlentyConnector\Connector\ValueObject\Mapping\Mapping;

interface MappingServiceInterface
{
    /**
     * @param null $objectType
     *
     * @return Mapping[]
     */
    public function getMappingInformation($objectType = null);
}
