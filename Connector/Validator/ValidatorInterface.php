<?php

namespace PlentyConnector\Connector\Validator;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface ValidatorInterface
 */
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
