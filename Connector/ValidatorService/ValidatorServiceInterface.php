<?php

namespace PlentyConnector\Connector\ValidatorService;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

interface ValidatorServiceInterface
{
    /**
     * @param ValidatorInterface $validator
     */
    public function addValidator(ValidatorInterface $validator);

    /**
     * @param TransferObjectInterface|ValueObjectInterface     $object
     * @param TransferObjectInterface[]|ValueObjectInterface[] $parents
     */
    public function validate($object, array $parents = []);
}
