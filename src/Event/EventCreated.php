<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EventCreated extends EventEvent
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var string
     */
    private $location;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var EventType
     */
    private $type;

    /**
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param \DateTime $date
     */
    public function __construct($eventId, Title $title, $location, \DateTime $date, EventType $type)
    {
        parent::__construct($eventId);

        $this->setTitle($title);
        $this->setLocation($location);
        $this->setDate($date);
        $this->setType($type);
    }

    /**
     * @param Title $title
     */
    private function setTitle(Title $title)
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
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return EventType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param EventType $type
     */
    private function setType($type) {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'location' => $this->getLocation(),
            'date' => $this->getDate()->format('c'),
            'title' => (string)$this->getTitle(),
            'type' => array(
              'id' => $this->type->getId(),
              'label' => $this->type->getLabel()
            )
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            new Title($data['title']),
            $data['location'],
            \DateTime::createFromFormat('c', $data['date']),
            new EventType(
              $data['type']['id'],
              $data['type']['label']
            )
        );
    }
}
