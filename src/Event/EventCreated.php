<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EventCreated extends EventEvent
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @param string $eventId
     * @param string $title
     * @param string $location
     * @param \DateTime $date
     */
    public function __construct($eventId, $title, $location, \DateTime $date)
    {
        parent::__construct($eventId);

        $this->setTitle($title);
        $this->setLocation($location);
        $this->setDate($date);
    }

    /**
     * @param string $title
     */
    private function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param \DateTime $date
     */
    private function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @param string $location
     */
    private function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return string
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
            'location' => $this->getLocation(),
            'date' => $this->getDate()->format('c'),
            'title' => $this->getTitle(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            $data['title'],
            $data['location'],
            \DateTime::createFromFormat('c', $data['date'])
        );
    }
}
