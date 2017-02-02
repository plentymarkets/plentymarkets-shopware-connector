<?php

namespace PlentyConnector\Connector\ValueObject\PropertyOption;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface PropertyOptionInterface
 */
interface PropertyOptionInterface extends TransferObjectInterface, TranslateableInterface
{
    /**
     * identifier of the property group
     *
     * @return string
     */
    public function getGroupIdentifier();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getValue();
}
