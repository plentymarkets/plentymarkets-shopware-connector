<?php

namespace PlentyConnector\tests\Unit\Adapter\PlentymarketsAdapter\Helper;

use PHPUnit\Framework\TestCase;
use PlentymarketsAdapter\Helper\ReferenceAmountCalculator;
use PlentymarketsAdapter\ReadApi\Item\Unit;

class ReferenceAmountCalculatorTest extends TestCase
{
    /**
     * @var ReferenceAmountCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $units = json_decode(file_get_contents(__DIR__ . '/Fixture/units.json'), true);

        $unitApi = $this->createMock(Unit::class);
        $unitApi->method('findAll')->willReturn($units);

        $this->calculator = new ReferenceAmountCalculator($unitApi);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [['unit' => ['unitId' => 3, 'content' => 100]], 100],
            [['unit' => ['unitId' => 3, 'content' => 250]], 100],
            [['unit' => ['unitId' => 3, 'content' => 251]], 1000],
            [['unit' => ['unitId' => 3, 'content' => 1100]], 1000],
            [['unit' => ['unitId' => 3, 'content' => 1050]], 1000],

            [['unit' => ['unitId' => 2, 'content' => 0.25]], 1],
            [['unit' => ['unitId' => 2, 'content' => 0.254]], 1.0],
            [['unit' => ['unitId' => 2, 'content' => 0.254]], 1.0],

            [['unit' => ['unitId' => 7, 'content' => 0.1]], 1.0],

            [['unit' => ['unitId' => 0, 'content' => 0.1]], 1.0],
        ];
    }

    /**
     * @param array $variation
     * @param float $expectedValue
     *
     * @dataProvider dataProvider
     */
    public function testReferenceAmountCalculation(array $variation, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->calculator->calculate($variation));
    }
}
