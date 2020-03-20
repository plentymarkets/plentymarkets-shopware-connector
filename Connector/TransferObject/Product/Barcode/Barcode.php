<?php

namespace SystemConnector\TransferObject\Product\Barcode;

use ReflectionClass;
use SystemConnector\ValueObject\AbstractValueObject;

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

    public function getType(): int
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

    public function getTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    public function getCode(): string
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
            'code' => $this->getCode(),
        ];
    }
}
