<?php


namespace CultuurNet\UDB3\Event;

class EventWasTagged extends EventEvent
{
    protected $keyword;

    public function __construct($eventId, $keyword)
    {
        parent::__construct($eventId);
        $this->keyword = $keyword;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'keyword' => $this->keyword,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], $data['keyword']);
    }
}
