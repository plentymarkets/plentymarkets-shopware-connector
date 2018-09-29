<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;

class GetAttributeTraitTest extends TestCase
{
    use GetAttributeTrait;

    public function testAttributeGeneration()
    {
        $attributes = $this->getAttributes(['key' => 'value']);

        $expected = new Attribute();
        $expected->setKey('key');
        $expected->setValue('value');

        self::assertEquals($expected, $attributes[0]);
    }
}
