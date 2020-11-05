<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

class Status
{
    private const SCHEDULED = 'scheduled';
    private const POSTPONED = 'postponed';
    private const CANCELLED = 'cancelled';

    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function scheduled(): Status
    {
        return new Status(self::SCHEDULED);
    }

    public static function postponed(): Status
    {
        return new Status(self::POSTPONED);
    }

    public static function cancelled(): Status
    {
        return new Status(self::CANCELLED);
    }

    public function toNative(): string
    {
        return $this->value;
    }

    public function equals(Status $status): bool
    {
        return $this->value === $status->toNative();
    }
}
