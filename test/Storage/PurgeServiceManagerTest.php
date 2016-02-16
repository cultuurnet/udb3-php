<?php

namespace CultuurNet\UDB3\Storage;

use PHPUnit_Framework_TestCase;

class PurgeServiceManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PurgeServiceManager
     */
    private $purgeServiceManager;

    protected function setUp()
    {
        $this->purgeServiceManager = new PurgeServiceManager();
    }

    /**
     * @test
     */
    public function it_can_store_an_array_of_ReadModel_PurgeServiceInterfaces()
    {
        $this->addReadModel_PurgeServiceInterfaces($this->purgeServiceManager);

        $this->assertEquals(2, count($this->purgeServiceManager->getReadModelPurgeServices()));
    }

    /**
     * @test
     */
    public function it_can_store_an_array_of_WriteModel_PurgeServiceInterfaces()
    {
        $this->addWriteModel_PurgeServiceInterfaces($this->purgeServiceManager);

        $this->assertEquals(2, count($this->purgeServiceManager->getWriteModelPurgeServices()));
    }

    /**
     * @test
     */
    public function it_can_store_an_array_of_Read_and_WriteModel_PurgeServiceInterfaces()
    {
        $this->addReadModel_PurgeServiceInterfaces($this->purgeServiceManager);
        $this->addWriteModel_PurgeServiceInterfaces($this->purgeServiceManager);

        $this->assertEquals(2, count($this->purgeServiceManager->getReadModelPurgeServices()));
        $this->assertEquals(2, count($this->purgeServiceManager->getWriteModelPurgeServices()));
    }

    /**
     * @param PurgeServiceManager $purgeServiceManager
     */
    private function addReadModel_PurgeServiceInterfaces(PurgeServiceManager $purgeServiceManager)
    {
        $purgeServiceManager->addReadModelPurgeService($this->createMockedPurgeService());
        $purgeServiceManager->addReadModelPurgeService($this->createMockedPurgeService());
    }

    /**
     * @param PurgeServiceManager $purgeServiceManager
     */
    private function addWriteModel_PurgeServiceInterfaces(PurgeServiceManager $purgeServiceManager)
    {
        $purgeServiceManager->addWriteModelPurgeService($this->createMockedPurgeService());
        $purgeServiceManager->addWriteModelPurgeService($this->createMockedPurgeService());
    }

    /**
     * @return PurgeServiceInterface
     */
    private function createMockedPurgeService()
    {
        return $this->getMock(PurgeServiceInterface::class);
    }
}
