<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Web\Url;

class UpdateUrl extends AbstractUpdateOrganizerCommand
{
    /**
     * @var Url
     */
    private $url;

    /**
     * UpdateUrl constructor.
     * @param string $organizerId
     * @param Url $url
     */
    public function __construct(
        $organizerId,
        Url $url
    ) {
        parent::__construct($organizerId);
        $this->url = $url;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
