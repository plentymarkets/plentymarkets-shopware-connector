<?php

namespace SystemConnector\TransferObject\Product\Badge;

use ReflectionClass;
use SystemConnector\ValueObject\AbstractValueObject;

class Badge extends AbstractValueObject
{
    const TYPE_HIGHLIGHT = 'highlight';

    /**
     * @var string
     */
    private $type = self::TYPE_HIGHLIGHT;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
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
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
        ];
    }
}
