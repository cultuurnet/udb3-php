<?php

namespace CultuurNet\UDB3\Calendar;

use Broadway\Serializer\SerializableInterface;

class OpeningHour implements SerializableInterface
{
    /**
     * @var OpeningTime
     */
    private $opens;

    /**
     * @var OpeningTime
     */
    private $closes;

    /**
     * @var DayOfWeek[]
     */
    private $daysOfWeek;

    /**
     * OpeningHour constructor.
     * @param OpeningTime $opens
     * @param OpeningTime $closes
     * @param DayOfWeek[] $daysOfWeek
     */
    public function __construct(
        OpeningTime $opens,
        OpeningTime $closes,
        DayOfWeek ...$daysOfWeek
    ) {
        $this->daysOfWeek = $daysOfWeek;
        $this->opens = $opens;
        $this->closes = $closes;
    }

    /**
     * @param DayOfWeek[] $dayOfWeeks
     */
    public function addDaysOfWeek(DayOfWeek ...$dayOfWeeks)
    {
        $this->daysOfWeek = array_merge($this->daysOfWeek, $dayOfWeeks);
    }

    /**
     * @return OpeningTime
     */
    public function getOpens()
    {
        return $this->opens;
    }

    /**
     * @return OpeningTime
     */
    public function getCloses()
    {
        return $this->closes;
    }

    /**
     * @return DayOfWeek[]
     */
    public function getDaysOfWeek()
    {
        return $this->daysOfWeek;
    }

    /**
     * @param OpeningHour $otherOpeningHour
     * @return bool
     */
    public function hasEqualHours(OpeningHour $otherOpeningHour)
    {
        return $otherOpeningHour->getOpens()->sameValueAs($this->getOpens()) &&
            $otherOpeningHour->getCloses()->sameValueAs($this->getCloses());
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        $weekDays = array_map(
            function ($dayOfWeek) {
                return DayOfWeek::fromNative($dayOfWeek);
            },
            $data['dayOfWeek']
        );

        return new static(
            OpeningTime::fromNativeString($data['opens']),
            OpeningTime::fromNativeString($data['closes']),
            ...$weekDays
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $serializedWeekDays = array_map(
            function (DayOfWeek $dayOfWeek) {
                return $dayOfWeek->getValue();
            },
            $this->daysOfWeek
        );

        return [
            'opens' => $this->opens->toNativeString(),
            'closes' => $this->closes->toNativeString(),
            'dayOfWeek' => $serializedWeekDays
        ];
    }
}
