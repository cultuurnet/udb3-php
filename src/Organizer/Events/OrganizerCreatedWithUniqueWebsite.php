<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class OrganizerCreatedWithUniqueWebsite extends OrganizerEvent
{
    /**
     * @var Url
     */
    protected $website;

    /**
     * @var Title
     */
    public $title;

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

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'website' => (string) $this->getWebsite(),
            'title' => (string) $this->getTitle(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['organizer_id'],
            Url::fromNative($data['website']),
            new Title($data['title'])
        );
    }
}
