<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address\Address;

interface AddressFormatterInterface
{
    /**
     * @param Address $address
     * @return string
     */
    public function format(Address $address);
}
