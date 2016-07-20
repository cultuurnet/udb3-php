<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address;
use Doctrine\Common\Cache\ArrayCache;

class CacheAddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayCache
     */
    private $cache;

    /**
     * @var CacheAddressRepository
     */
    private $repository;

    /**
     * @var Address
     */
    private $addressLeuven;

    /**
     * @var Address
     */
    private $addressBrussel;

    public function setUp()
    {
        $this->cache = new ArrayCache();
        $this->repository = new CacheAddressRepository($this->cache);

        $this->addressLeuven = new Address(
            'Martelarenlaan 1',
            '3000',
            'Leuven',
            'BE'
        );

        $this->addressBrussel = new Address(
            'Wetstraat 1',
            '1000',
            'Brussel',
            'BE'
        );
    }

    /**
     * @test
     */
    public function it_can_store_and_retrieve_multiple_address()
    {
        $leuvenId = 1;
        $brusselId = 2;

        $this->repository->save($leuvenId, $this->addressLeuven);
        $this->repository->save($brusselId, $this->addressBrussel);

        $this->assertEquals($this->addressLeuven, $this->repository->get($leuvenId));
        $this->assertEquals($this->addressBrussel, $this->repository->get($brusselId));
    }

    /**
     * @test
     */
    public function it_can_mark_address_as_removed()
    {
        $leuvenId = 1;

        $this->repository->save($leuvenId, $this->addressLeuven);
        $this->repository->remove($leuvenId);

        $this->setExpectedException(AddressGoneException::class);

        $this->repository->get($leuvenId);
    }
}
