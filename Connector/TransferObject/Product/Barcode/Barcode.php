<?php

namespace PlentyConnector\Connector\TransferObject\Product\Barcode;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Barcode
 */
class Barcode extends AbstractValueObject
{
    const TYPE_GTIN13 = 1;
    const TYPE_GTIN128 = 2;
    const TYPE_UPC = 3;
    const TYPE_ISBN = 4;

    /**
     * @var integer
     */
    private $type = 1;

    /**
     * @var string
     */
    private $code = '';

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $oClass = new \ReflectionClass(__CLASS__);
        $possibleValues =  $oClass->getConstants();

        Assertion::inArray($type, $possibleValues);

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        Assertion::notBlank($code);

        $this->code = $code;
    }
}
