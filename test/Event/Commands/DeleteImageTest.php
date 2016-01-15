<?php

namespace CultuurNet\UDB3\Event\Commands;

class DeleteImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteImage
     */
    protected $deleteImage;

    public function setUp()
    {
        $this->deleteImage = new DeleteImage('id', 'indexToDelete', 'internalId');
    }

    /**
     * @test
     */
    public function it_is_possible_to_instantiate_the_command_with_parameters()
    {
        $expectedDeleteImage = new DeleteImage('id', 'indexToDelete', 'internalId');

        $this->assertEquals($expectedDeleteImage, $this->deleteImage);
    }
}
