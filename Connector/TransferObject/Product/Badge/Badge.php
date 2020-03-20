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

    public function getType(): string
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

    public function getTypes(): array
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
