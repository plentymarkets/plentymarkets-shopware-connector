<?php

namespace ShopwareAdapter\ResponseParser\Order;

interface OrderResponseParserInterface
{
    public function parse(array $entry): array;
}
