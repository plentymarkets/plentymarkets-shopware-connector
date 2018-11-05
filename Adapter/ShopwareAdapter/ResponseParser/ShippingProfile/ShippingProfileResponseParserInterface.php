<?php

namespace ShopwareAdapter\ResponseParser\ShippingProfile;

use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;

interface ShippingProfileResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|ShippingProfile
     */
    public function parse(array $entry);
}
