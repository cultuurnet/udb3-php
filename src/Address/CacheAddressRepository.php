<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address;
use Doctrine\Common\Cache\Cache;

class CacheAddressRepository implements AddressRepositoryInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $id
     * @return Address
     *
     * @throws AddressGoneException
     */
    public function get($id)
    {
        $serialized = $this->cache->fetch($id);

        if ($serialized == 'GONE') {
            throw new AddressGoneException();
        }

        return Address::deserialize(
            json_decode($serialized, true)
        );
    }

    /**
     * @param string $id
     * @param Address $address
     */
    public function save($id, Address $address)
    {
        $json = json_encode($address->serialize());
        $this->cache->save($id, $json);
    }

    /**
     * @param string $id
     */
    public function remove($id)
    {
        $this->cache->save($id, 'GONE');
    }

}
