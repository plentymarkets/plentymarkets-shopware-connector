<?php

namespace SystemConnector\ValidatorService;

use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\ValueObjectInterface;

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
