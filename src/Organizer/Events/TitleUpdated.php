<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Title;

final class TitleUpdated extends OrganizerEvent
{
    /**
     * @var Title
     */
    private $title;

    /**
     * TitleUpdated constructor.
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

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
                'title' => $this->getTitle()->toNative(),
            ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['organizer_id'],
            new Title($data['title'])
        );
    }
}
