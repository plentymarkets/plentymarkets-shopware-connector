<?php

namespace PlentyConnector\Connector\Workflow;

/**
 * Interface DefinitionInterface
 *
 * @package PlentyConnector\Connector\Workflow
 */
interface DefinitionInterface
{
    /**
     * @return string
     */
    public function getOriginAdapterName();

    /**
     * @return string
     */
    public function getDestinationAdapterName();

    /**
     * @return string
     */
    public function getObjectType();
}
