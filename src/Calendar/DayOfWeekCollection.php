<?php

namespace CultuurNet\UDB3\Calendar;

use Broadway\Serializer\SerializableInterface;

class DayOfWeekCollection implements SerializableInterface
{
    /**
     * @var string[]
     */
    private $daysOfWeek = [];

    /**
     * DayOfWeekCollection constructor.
     * @param DayOfWeek|null $dayOfWeek
     */
    public function __construct(DayOfWeek $dayOfWeek = null)
    {
        if ($dayOfWeek !== null) {
            $this->addDayOfWeek($dayOfWeek);
        }
    }

    /**
     * Keeps the collection of days of week unique.
     * Makes sure that the objects are stored as strings to allow PHP serialize method.
     *
     * @param DayOfWeek $dayOfWeek
     * @return DayOfWeekCollection
     */
    public function addDayOfWeek(DayOfWeek $dayOfWeek)
    {
        $this->daysOfWeek = array_unique(
            array_merge(
                $this->daysOfWeek,
                [
                    $dayOfWeek->toNative(),
                ]
            )
        );

        return $this;
    }

    /**
     * @return DayOfWeek[]
     */
    public function getDaysOfWeek()
    {
        return array_map(
            function ($dayOfWeek) {
                return DayOfWeek::fromNative($dayOfWeek);
            },
            $this->daysOfWeek
        );
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        $dayOfWeekCollection = new DayOfWeekCollection();

        foreach ($data as $dayOfWeek) {
            $dayOfWeekCollection->addDayOfWeek(
                DayOfWeek::fromNative($dayOfWeek)
            );
        }

        return $dayOfWeekCollection;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return $this->daysOfWeek;
    }
}
