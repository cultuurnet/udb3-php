<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use Broadway\Serializer\SerializableInterface;
use InvalidArgumentException;

class Status implements SerializableInterface
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

    public static function fromNative(string $value): Status
    {
        return new Status($value);
    }

    public function equals(Status $status): bool
    {
        return $this->value === $status->toNative();
    }

    public static function deserialize(array $data): Status
    {
        return new Status($data['eventStatus']);
    }

    public function serialize(): array
    {
        return [
            'eventStatus' => $this->value,
        ];
    }
}
