<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Variations\Model\Properties\Description;

class UpdateDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateDescription
     */
    protected $updateDescription;

    public function setUp()
    {
        $this->updateDescription = new UpdateDescription(
            'id',
            new Description('La description'),
            new Language('fr')
        );
    }

    /**
     * @test
     */
    public function it_is_possible_to_instantiate_the_command_with_parameters()
    {
        $expectedUpdateDescription = new UpdateDescription(
            'id',
            new Description('La description'),
            new Language('fr')
        );

        $this->assertEquals($expectedUpdateDescription, $this->updateDescription);
    }
}
