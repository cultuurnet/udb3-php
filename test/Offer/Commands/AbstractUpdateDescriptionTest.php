<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;

class AbstractUpdateDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractUpdateDescription|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateDescriptionCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Language
     */
    protected $language;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->description = 'This is the event description update.';
        $this->language = new Language('en');

        $this->updateDescriptionCommand = $this->getMockForAbstractClass(
            AbstractUpdateDescription::class,
            array($this->itemId, $this->description, $this->language)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $description = $this->updateDescriptionCommand->getDescription();
        $expectedDescription = 'This is the event description update.';

        $this->assertEquals($expectedDescription, $description);

        $itemId = $this->updateDescriptionCommand->getItemId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }

    /**
     * @test
     */
    public function it_should_keep_track_of_the_description_language()
    {
        $this->assertEquals(new Language('en'), $this->updateDescriptionCommand->getLanguage());
    }
}
