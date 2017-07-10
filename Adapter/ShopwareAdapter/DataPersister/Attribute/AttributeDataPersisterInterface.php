<?php

namespace ShopwareAdapter\DataPersister\Attribute;

/**
 * Interface AttributeDataPersisterInterface
 */
interface AttributeDataPersisterInterface
{
    /**
     * @param int $identifier
     * @param Attribute[] $attributes
     * @param string $table
     */
    public function saveAttributes($identifier, array $attributes, $table);
}
