<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Facility;

class UpdateFacilitiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateFacilities
     */
    protected $updateFacilities;

    public function setUp()
    {
        $facilities = [
            new Facility('facility1', 'facility label'),
        ];

        $this->updateFacilities = new UpdateFacilities(
            'id',
            $facilities
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'id';
        $expectedFacilities = [
            new Facility('facility1', 'facility label'),
        ];

        $this->assertEquals($expectedId, $this->updateFacilities->getItemId());
        $this->assertEquals($expectedFacilities, $this->updateFacilities->getFacilities());
    }
}