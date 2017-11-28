<?php

namespace PlentymarketsAdapter\Helper;

use PlentymarketsAdapter\ReadApi\Item\Unit as UnitApi;

/**
 * Class ReferenceAmountCalculator
 */
class ReferenceAmountCalculator implements ReferenceAmountCalculatorInterface
{
    /**
     * @var UnitApi
     */
    private $itemUnitApi;

    /**
     * @var array
     */
    private static $units = [];

    /**
     * @var array
     */
    private static $convertionMatrix = [
        'KGM' => ['conversion' => 1], // kilogram
        'GRM' => ['conversion' => 0.001], // gram
        'MGM' => ['conversion' => 0.000001], // milligram

        'LTR' => ['conversion' => 1], // liter
        'MLT' => ['conversion' => 0.001], // millilitre

        'MTQ' => ['conversion' => 1], // cubic metre
        'CMQ' => ['conversion' => 0.0001], // cubic centimetre

        'MTK' => ['conversion' => 1], // square metre
        'CMK' => ['conversion' => 0.0001], // square centimetre

        'MTR' => ['conversion' => 1], // metre
        'CMT' => ['conversion' => 0.01], // centimetre
        'MMT' => ['conversion' => 0.001], // millimetre
    ];

    /**
     * ReferenceAmountCalculator constructor.
     *
     * @param UnitApi $itemUnitApi
     */
    public function __construct(UnitApi $itemUnitApi)
    {
        $this->itemUnitApi = $itemUnitApi;
    }

    /**
     * @param array $variation
     *
     * @return float
     */
    public function calculate(array $variation)
    {
        if (empty(self::$units)) {
            self::$units = array_filter($this->itemUnitApi->findAll(), function (array $unit) {
                return array_key_exists($unit['unitOfMeasurement'], self::$convertionMatrix);
            });
        }

        $variationUnit = $this->getUnitOfVariation($variation);

        if (null === $variationUnit) {
            return 1.0;
        }

        $modifier = self::$convertionMatrix[$variationUnit]['conversion'];

        $content = $variation['unit']['content'] * $modifier;

        if ($content <= 0.25) {
            return 0.1 / $modifier;
        }

        return 1.0 / $modifier;
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
