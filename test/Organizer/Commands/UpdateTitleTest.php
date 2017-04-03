<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Title;

class UpdateTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $organizerId;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var UpdateTitle
     */
    private $updateTitle;

    protected function setUp()
    {
        $this->organizerId = '3c16f422-33ea-4a5b-b70c-dd22b9fddcba';

        $this->title = new Title('Het Depot');

        $this->updateTitle = new UpdateTitle(
            $this->organizerId,
            $this->title
        );
    }

    /**
     * @test
     */
    public function it_stores_an_organizer_id()
    {
        $this->assertEquals(
            $this->organizerId,
            $this->updateTitle->getOrganizerId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_title()
    {
        $this->assertEquals(
            $this->title,
            $this->updateTitle->getTitle()
        );
    }
}
