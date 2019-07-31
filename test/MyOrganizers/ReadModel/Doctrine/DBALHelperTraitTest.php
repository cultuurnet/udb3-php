<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use PHPUnit\Framework\TestCase;

class DBALHelperTraitTest extends TestCase
{
    /**
     * @var \DBALHelperTrait
     */
    private $dbalHelper;

    public function setUp()
    {
        $this->dbalHelper =
            $this->getMockBuilder(DBALHelperTrait::class)
                ->getMockForTrait();
    }

    /**
     * @test
     */
    public function it_can_return_a_pdo_parameter()
    {
        $this->assertEquals(
            ':foo',
            $this->dbalHelper->parameter('foo')
        );
    }
}
