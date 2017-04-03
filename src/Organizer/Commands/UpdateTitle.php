<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Title;

class UpdateTitle extends AbstractUpdateOrganizerCommand
{
    /**
     * @var Title
     */
    private $title;

    /**
     * UpdateTitle constructor.
     * @param string $organizerId
     * @param Title $title
     */
    public function __construct(
        $organizerId,
        Title $title
    ) {
        parent::__construct($organizerId);
        $this->title = $title;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }
}
