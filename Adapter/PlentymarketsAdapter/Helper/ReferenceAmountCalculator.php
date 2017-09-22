<?php

namespace PlentymarketsAdapter\Helper;

use PlentymarketsAdapter\ReadApi\Item\Unit as UnitApi;

/**
 * Class ReferenceAmountCalculator
 */
class ReferenceAmountCalculator implements ReferenceAmountCalculatorInterface
{
    /**
     * @var array
     */
    private static $units;

    /**
     * @var array
     */
    private static $convertionMatrix = [
        'KGM' => ['base' => 'KGM', 'conversion' => 1], // kilogram
        'GRM' => ['base' => 'KGM', 'conversion' => 0.001], // gram
        'MGM' => ['base' => 'KGM', 'conversion' => 0.000001], // milligram

        'LTR' => ['base' => 'LTR', 'conversion' => 1], // liter
        'MLT' => ['base' => 'LTR', 'conversion' => 0.001], // millilitre

        'MTQ' => ['base' => 'MTQ', 'conversion' => 1], // cubic metre
        'CMQ' => ['base' => 'MTQ', 'conversion' => 0.0001], // cubic centimetre

        'MTK' => ['base' => 'MTK', 'conversion' => 1], // square metre
        'CMK' => ['base' => 'MTK', 'conversion' => 0.0001], // square centimetre

        'MTR' => ['base' => 'MTR', 'conversion' => 1], // metre
        'CMT' => ['base' => 'MTR', 'conversion' => 0.01], // centimetre
        'MMT' => ['base' => 'MTR', 'conversion' => 0.001], // millimetre
    ];

    /**
     * ReferenceAmountCalculator constructor.
     *
     * @param UnitApi $itemUnitApi
     */
    public function __construct(UnitApi $itemUnitApi)
    {
        self::$units = array_filter($itemUnitApi->findAll(), function (array $unit) {
            return array_key_exists($unit['unitOfMeasurement'], self::$convertionMatrix);
        });
    }

    /**
     * @param array $variation
     *
     * @return float
     */
    public function calculate(array $variation)
    {
        $variationUnit = $this->getUnitOfVariation($variation);

        if (null === $variationUnit) {
            return 1.0;
        }

        $modifier = self::$convertionMatrix[$variationUnit]['conversion'];

        $content = $variation['unit']['content'] * $modifier;

        if ($content <= 0.25) {
            return 0.1 / $modifier;
        }

        return 1.0;
    }

    /**
     * @param array $variation
     *
     * @return null|string
     */
    private function getUnitOfVariation(array $variation)
    {
        foreach (self::$units as $unit) {
            if ((int) $unit['id'] === (int) $variation['unit']['unitId']) {
                return $unit['unitOfMeasurement'];
            }
        }

        return null;
    }
}
