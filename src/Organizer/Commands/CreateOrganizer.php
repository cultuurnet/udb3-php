<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Organizer\Commands\AbstractOrganizerCommand;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class CreateOrganizer extends AbstractOrganizerCommand
{
    /**
     * @var Url
     */
    private $website;

    /**
     * @var Title
     */
    private $title;

    /**
     * @param string $id
     * @param Url $website
     * @param Title $title
     */
    public function __construct(
        $id,
        Url $website,
        Title $title
    ) {
        parent::__construct($id);

        $this->website = $website;
        $this->title = $title;
    }

    /**
     * @return Url
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }
}
