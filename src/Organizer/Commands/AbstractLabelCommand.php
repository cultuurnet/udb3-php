<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Identity\UUID;

abstract class AbstractLabelCommand extends AbstractOrganizerCommand
{
    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @param string $organizerId
     * @param UUID $labelId
     */
    public function __construct(
        $organizerId,
        UUID $labelId
    ) {
        parent::__construct($organizerId);
        $this->labelId = $labelId;
    }

    /**
     * @return UUID
     */
    public function getLabelId()
    {
        return $this->labelId;
    }
}
