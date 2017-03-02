<?php


namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;


/**
 * Class GetAttributeTraitTest
 *
 * @group ResponseParser
 */
class GetAttributeTraitTest extends TestCase
{
    use GetAttributeTrait;

    /**
     * @return void
     */
    public function testAttributeGeneration()
    {
        $attributes = $this->getAttributes(['key' => 'value']);
        $expected = new Attribute();
        $expected->setKey('key');
        $expected->setValue('value');

        $this->assertEquals($expected, $attributes[0]);

    }
}