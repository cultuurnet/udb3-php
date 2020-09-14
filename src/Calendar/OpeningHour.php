<?php

namespace CultuurNet\UDB3\Calendar;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\OpeningHour as Udb3ModelOpeningHour;

/**
 * @todo Replace by CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\OpeningHour.
 */
final class OpeningHour implements SerializableInterface
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
     * @var DayOfWeekCollection
     */
    private $dayOfWeekCollection;

    /**
     * OpeningHour constructor.
     * @param OpeningTime $opens
     * @param OpeningTime $closes
     * @param DayOfWeekCollection $dayOfWeekCollection
     */
    public function __construct(
        OpeningTime $opens,
        OpeningTime $closes,
        DayOfWeekCollection $dayOfWeekCollection
    ) {
        $this->dayOfWeekCollection = $dayOfWeekCollection;
        $this->opens = $opens;
        $this->closes = $closes;
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
     * @return DayOfWeekCollection
     */
    public function getDayOfWeekCollection()
    {
        return $this->dayOfWeekCollection;
    }

    /**
     * @param DayOfWeekCollection $dayOfWeekCollection
     */
    public function addDayOfWeekCollection(DayOfWeekCollection $dayOfWeekCollection)
    {
        foreach ($dayOfWeekCollection->getDaysOfWeek() as $dayOfWeek) {
            $this->dayOfWeekCollection->addDayOfWeek($dayOfWeek);
        }
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
        return new static(
            OpeningTime::fromNativeString($data['opens']),
            OpeningTime::fromNativeString($data['closes']),
            DayOfWeekCollection::deserialize($data['dayOfWeek'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'opens' => $this->opens->toNativeString(),
            'closes' => $this->closes->toNativeString(),
            'dayOfWeek' => $this->dayOfWeekCollection->serialize(),
        ];
    }

    /**
     * @param Udb3ModelOpeningHour $openingHour
     * @return self
     */
    public static function fromUdb3ModelOpeningHour(Udb3ModelOpeningHour $openingHour)
    {
        return new self(
            OpeningTime::fromUdb3ModelTime($openingHour->getOpeningTime()),
            OpeningTime::fromUdb3ModelTime($openingHour->getClosingTime()),
            DayOfWeekCollection::fromUdb3ModelDays($openingHour->getDays())
        );
    }
}
