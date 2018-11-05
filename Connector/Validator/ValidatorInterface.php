<?php

namespace SystemConnector\Validator;

use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValueObject\ValueObjectInterface;

interface ValidatorInterface
{
    /**
     * @param TransferObjectInterface|ValueObjectInterface $object
     *
     * @return bool
     */
    public function supports($object);

    /**
     * @param TransferObjectInterface|ValueObjectInterface $object
     */
    public function validate($object);
}
