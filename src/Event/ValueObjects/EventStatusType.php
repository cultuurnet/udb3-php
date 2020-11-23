<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use InvalidArgumentException;

//Taken from schema.org: https://schema.org/EventStatusType
final class EventStatusType
{
    private const SCHEDULED = 'EventScheduled';
    private const POSTPONED = 'EventPostponed';
    private const CANCELLED = 'EventCancelled';

    /**
     * @var string
     */
    private $value;

    /**
     * @var string[]
     */
    private const ALLOWED_VALUES = [
        self::SCHEDULED,
        self::POSTPONED,
        self::CANCELLED,
    ];

    private function __construct(string $value)
    {
        if (!\in_array($value, self::ALLOWED_VALUES, true)) {
            throw new InvalidArgumentException('Status does not support the value "' . $value . '"');
        }
        $this->value = $value;
    }

    public static function scheduled(): EventStatusType
    {
        return new EventStatusType(self::SCHEDULED);
    }

    public static function postponed(): EventStatusType
    {
        return new EventStatusType(self::POSTPONED);
    }

    public static function cancelled(): EventStatusType
    {
        return new EventStatusType(self::CANCELLED);
    }

    public function toNative(): string
    {
        return $this->value;
    }

    public static function fromNative(string $value): EventStatusType
    {
        return new EventStatusType($value);
    }

    public function equals(EventStatusType $status): bool
    {
        return $this->value === $status->toNative();
    }
}
