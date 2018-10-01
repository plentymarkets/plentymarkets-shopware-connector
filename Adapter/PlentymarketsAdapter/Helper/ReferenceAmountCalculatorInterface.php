<?php

namespace PlentymarketsAdapter\Helper;

interface ReferenceAmountCalculatorInterface
{
    /**
     * @param array $variation
     *
     * @return float
     */
    public function calculate(array $variation);
}
