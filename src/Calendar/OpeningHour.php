<?php

namespace CultuurNet\UDB3\Calendar;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\WeekDay;

class OpeningHour implements SerializableInterface
{
    const FORMAT = 'H:i:s';

    /**
     * @var Time
     */
    private $opens;

    /**
     * @var Time
     */
    private $closes;

    /**
     * @var WeekDay[]
     */
    private $weekDays;

    /**
     * OpeningHour constructor.
     * @param Time $opens
     * @param Time $closes
     * @param WeekDay[] $weekDays
     */
    public function __construct(
        Time $opens,
        Time $closes,
        WeekDay ...$weekDays
    ) {
        $this->weekDays = $weekDays;
        $this->opens = $opens;
        $this->closes = $closes;
    }

    /**
     * @return Time
     */
    public function getOpens()
    {
        return $this->opens;
    }

    /**
     * @return Time
     */
    public function getCloses()
    {
        return $this->closes;
    }

    /**
     * @return WeekDay[]
     */
    public function getWeekDays()
    {
        return $this->weekDays;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        $weekDays = array_map(
            function ($dayOfWeek) {
                return WeekDay::fromNative($dayOfWeek);
            },
            $data['dayOfWeek']
        );

        return new static(
            Time::fromNativeDateTime(
                \DateTime::createFromFormat(self::FORMAT, $data['opens'])
            ),
            Time::fromNativeDateTime(
                \DateTime::createFromFormat(self::FORMAT, $data['closes'])
            ),
            ...$weekDays
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $serializedWeekDays = array_map(
            function (WeekDay $weekDay) {
                return $weekDay->getValue();
            },
            $this->weekDays
        ) ;

        return [
            'opens' => $this->opens->toNativeDateTime()->format(self::FORMAT),
            'closes' => $this->closes->toNativeDateTime()->format(self::FORMAT),
            'dayOfWeek' => $serializedWeekDays
        ];
    }
}
