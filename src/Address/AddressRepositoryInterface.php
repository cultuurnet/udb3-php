<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address;

interface AddressRepositoryInterface
{
    /**
     * @param string $id
     * @return Address
     *
     * @throws AddressGoneException
     */
    public function get($id);

    /**
     * @param string $id
     * @param Address $address
     */
    public function save($id, Address $address);

    /**
     * @param string $id
     */
    public function remove($id);
}
