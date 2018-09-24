<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Web\Url;

class WebsiteUpdated extends OrganizerEvent
{
    /**
     * @var Url
     */
    private $website;

    /**
     * WebsiteUpdated constructor.
     * @param string $organizerId
     * @param Url $website
     */
    public function __construct(
        $organizerId,
        Url $website
    ) {
        parent::__construct($organizerId);
        $this->website = $website;
    }

    /**
     * @return Url
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
                'website' => (string) $this->getWebsite(),
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
            Url::fromNative($data['website'])
        );
    }
}
