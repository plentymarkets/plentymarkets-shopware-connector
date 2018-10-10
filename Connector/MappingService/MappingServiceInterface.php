<?php

namespace SystemConnector\MappingService;

use SystemConnector\ValueObject\Mapping\Mapping;

interface MappingServiceInterface
{
    /**
     * @param null $objectType
     *
     * @return Mapping[]
     */
    public function getMappingInformation($objectType = null);
}
