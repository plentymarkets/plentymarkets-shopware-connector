<?php

namespace SystemConnector\MappingService;

use SystemConnector\MappingService\Struct\Mapping;

interface MappingServiceInterface
{
    /**
     * @param null $objectType
     *
     * @return Mapping[]
     */
    public function getMappingInformation($objectType = null);
}
