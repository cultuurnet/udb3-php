<?php

namespace CultuurNet\UDB3\Storage;

use PHPUnit_Framework_MockObject_MockObject;
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

        $this->assertCount(2, $this->purgeServiceManager->getReadModelPurgeServices());
    }

    /**
     * @test
     */
    public function it_can_store_an_array_of_WriteModel_PurgeServiceInterfaces()
    {
        $this->addWriteModel_PurgeServiceInterfaces($this->purgeServiceManager);

        $this->assertCount(2, $this->purgeServiceManager->getWriteModelPurgeServices());
    }

    /**
     * @test
     */
    public function it_can_store_an_array_of_Read_and_WriteModel_PurgeServiceInterfaces()
    {
        $this->addReadModel_PurgeServiceInterfaces($this->purgeServiceManager);
        $this->addWriteModel_PurgeServiceInterfaces($this->purgeServiceManager);

        $this->assertCount(2, $this->purgeServiceManager->getReadModelPurgeServices());
        $this->assertCount(2, $this->purgeServiceManager->getWriteModelPurgeServices());
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
     * @return PurgeServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockedPurgeService()
    {
        return $this->createMock(PurgeServiceInterface::class);
    }
}
