<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Web\Url;

class UrlUpdated extends OrganizerEvent
{
    /**
     * @var Url
     */
    private $url;

    /**
     * UrlUpdated constructor.
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

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
                'url' => (string) $this->getUrl()
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
            Url::fromNative($data['url'])
        );
    }
}
