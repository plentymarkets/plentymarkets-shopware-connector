<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;

/**
 * Interface ShippingProfileResponseParserInterface.
 */
interface ShippingProfileResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|ShippingProfile
     */
    public function parse(array $entry);
}
