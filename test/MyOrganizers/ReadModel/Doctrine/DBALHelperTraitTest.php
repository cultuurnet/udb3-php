<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

class DBALHelperTraitTest extends \PHPUnit_Framework_TestCase
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
