<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Facility;

abstract class AbstractFacilitiesUpdated extends AbstractEvent
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
    final public function __construct(string $id, array $facilities)
    {
        parent::__construct($id);
        $this->facilities = $facilities;
    }

    /**
     * @return array
     */
    public function getFacilities(): array
    {
        return $this->facilities;
    }

    /**
     * @param array $data
     * @return mixed The object instance
     */
    public static function deserialize(array $data): AbstractFacilitiesUpdated
    {

        $facilities = array();
        foreach ($data['facilities'] as $facility) {
            $facilities[] = Facility::deserialize($facility);
        }

        return new static($data['item_id'], $facilities);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
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
