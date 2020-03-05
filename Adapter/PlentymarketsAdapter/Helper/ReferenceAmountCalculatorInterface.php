<?php

namespace PlentymarketsAdapter\Helper;

interface ReferenceAmountCalculatorInterface
{
    public function calculate(array $variation): float;
}
