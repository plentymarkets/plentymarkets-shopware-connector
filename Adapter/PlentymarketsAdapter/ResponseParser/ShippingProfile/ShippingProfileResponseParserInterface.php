<?php

namespace PlentymarketsAdapter\ResponseParser\ShippingProfile;

use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfileInterface;

/**
 * Interface ShippingProfileResponseParserInterface
 */
interface ShippingProfileResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return ShippingProfileInterface|null
     */
    public function parse(array $entry);
}
