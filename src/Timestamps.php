<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Timestamps.
 */

namespace CultuurNet\UDB3;

/**
 * Provices a class for calendar type: timestamps.
 */
class Timestamps implements CalendarInterface
{

    const TYPE = 'timestamps';

    /**
     * @var \CultuurNet\UDB3\Timestamp[]
     */
    protected $timestamps = array();

    public function __construct($timestamps = array())
    {
        $this->timestamps = $timestamps;
    }

    /**
     * Get current calendar type.
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    public function getTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Add a timestamp to the calendar.
     * @param \CultuurNet\UDB3\Timestamp $timestamp
     */
    public function addTimestamp(Timestamp $timestamp)
    {
        $this->timestamps[] = $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'type' => $this->getType(),
          'timestamps' => $this->timestamps,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
                $data['timestamps']
        );
    }

}
