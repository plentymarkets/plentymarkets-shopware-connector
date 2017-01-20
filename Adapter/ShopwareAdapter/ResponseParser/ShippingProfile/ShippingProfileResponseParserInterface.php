<?php

namespace ShopwareAdapter\ResponseParser\ShippingProfile;

/**
 * Interface ShippingProfileResponseParserInterface
 */
interface ShippingProfileResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return ShippingProfileInterfaceProf|null
     */
    public function parse(array $entry);
}
