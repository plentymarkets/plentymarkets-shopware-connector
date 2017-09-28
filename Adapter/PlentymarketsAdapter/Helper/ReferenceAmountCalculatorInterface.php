<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Interface ReferenceAmountCalculatorInterface
 */
interface ReferenceAmountCalculatorInterface
{
    /**
     * @param array $variation
     *
     * @return float
     */
    public function calculate(array $variation);
}
