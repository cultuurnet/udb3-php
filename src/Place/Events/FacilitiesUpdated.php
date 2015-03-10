<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\FacilitiesUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Description of DescriptionUpdated
 */
class FacilitiesUpdated extends PlaceEvent
{
    /**
     * The new facilities.
     * @var array
     */
    protected $facilities;

    /**
     * @param string $id
     * @param array $facilities
     */
    public function __construct($id, array $facilities)
    {
        parent::__construct($id);
        $this->facilities = $facilities;
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {

        $facilities = array();
        foreach ($data['facilities'] as $facility) {
            $facilities[] = \CultuurNet\UDB3\Facility::deserialize($facility);
        }

        return new static($data['place_id'], $facilities);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {

        $facilities = array();
        foreach ($this->facilities as $facility) {
            $facilities[] = $facility->serialize();
        }

        return parent::serialize() + array(
            'facilities' => $facilities,
        );
    }
}
