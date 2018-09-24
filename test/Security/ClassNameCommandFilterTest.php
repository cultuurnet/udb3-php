<?php

namespace CultuurNet\UDB3\Security;

use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Role\Commands\AddLabel;
use ValueObjects\StringLiteral\StringLiteral;

class ClassNameCommandFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateFacilities
     */
    private $updateFacilities;

    protected function setUp()
    {
        $this->updateFacilities = new UpdateFacilities(
            '26e36905-64d0-4cac-ba41-6d6dcd997ca0',
            [
                'facility1',
                'facility2',
            ]
        );
    }

    /**
     * @test
     */
    public function it_returns_true_when_class_name_matches()
    {
        $classNameCommandFilter = new ClassNameCommandFilter(
            new StringLiteral(UpdateFacilities::class),
            new StringLiteral(AddLabel::class)
        );

        $this->assertTrue($classNameCommandFilter->matches($this->updateFacilities));
    }

    /**
     * @test
     */
    public function it_returns_false_when_class_name_does_not_match()
    {
        $classNameCommandFilter = new ClassNameCommandFilter(
            new StringLiteral(AddLabel::class)
        );

        $this->assertFalse($classNameCommandFilter->matches($this->updateFacilities));
    }
}
