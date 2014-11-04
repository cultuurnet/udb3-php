<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


class EventImportedFromUDB2 extends EventEvent
{
    protected $cdbXml;

    public function __construct($eventId, $cdbXml)
    {
        parent::__construct($eventId);
        $this->cdbXml = $cdbXml;
    }

    public function getCdbXml()
    {
        return $this->cdbXml;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'cdbxml' => $this->cdbXml,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], $data['cdbxml']);
    }
}
