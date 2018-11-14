<?php

namespace SystemConnector\ValidatorService;

use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValueObject\ValueObjectInterface;

interface ValidatorServiceInterface
{
    /**
     * @param TransferObjectInterface|ValueObjectInterface     $object
     * @param TransferObjectInterface[]|ValueObjectInterface[] $parents
     */
    public function validate($object, array $parents = []);
}
