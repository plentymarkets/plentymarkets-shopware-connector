<?php

namespace ShopwareAdapter\ResponseParser\ShippingProfile;

/**
 * Interface ShippingProfileResponseParserInterface.
 */
interface ShippingProfileResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|ShippingProfileProf
     */
    public function parse(array $entry);
}
