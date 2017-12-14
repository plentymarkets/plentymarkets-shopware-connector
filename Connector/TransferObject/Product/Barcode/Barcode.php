<?php

namespace PlentyConnector\Connector\TransferObject\Product\Barcode;

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
     * @var int
     */
    private $type = self::TYPE_GTIN13;

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
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $reflection = new \ReflectionClass(__CLASS__);

        return $reflection->getConstants();
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
        $this->code = $code;
    }
}
