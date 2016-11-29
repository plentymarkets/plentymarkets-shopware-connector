<?php

namespace PlentyConnector\Connector\QueryBus;

use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;

/**
 * Class QueryFactoryInterface.
 */
interface QueryFactoryInterface
{
    /**
     * @param QueryGeneratorInterface $generator
     */
    public function addGenerator(QueryGeneratorInterface $generator);

    /**
     * @return mixed
     */
    public function create();
}
