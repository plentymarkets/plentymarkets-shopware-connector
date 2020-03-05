<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;

interface ShippingProfileResponseParserInterface
{
    /**
     * @return null|ShippingProfile
     */
    public function parse(array $entry);
}
