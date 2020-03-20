<?php

namespace PlentymarketsAdapter\Helper;

interface VariationHelperInterface
{
    public function getShopIdentifiers(array $variation): array;

    public function getMappedPlentyClientIds(): array;

    public function getMainVariation(array $variations): array;

    public function getMainVariationNumber(array $mainVariation, array $variations = []): string;
}
