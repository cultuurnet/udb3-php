<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use InvalidArgumentException;

final class StatusType
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

    public static function scheduled(): StatusType
    {
        return new StatusType(self::SCHEDULED);
    }

    public static function postponed(): StatusType
    {
        return new StatusType(self::POSTPONED);
    }

    public static function cancelled(): StatusType
    {
        return new StatusType(self::CANCELLED);
    }

    public function toNative(): string
    {
        return $this->value;
    }

    public static function fromNative(string $value): StatusType
    {
        return new StatusType($value);
    }

    public function equals(StatusType $status): bool
    {
        return $this->value === $status->toNative();
    }
}
