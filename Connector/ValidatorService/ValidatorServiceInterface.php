<?php

namespace SystemConnector\ValidatorService;

interface ValidatorServiceInterface
{
    /**
     * @param mixed   $object
     * @param mixed[] $parents
     */
    public function validate($object, array $parents = []);
}
