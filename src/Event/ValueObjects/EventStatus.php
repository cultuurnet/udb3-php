<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Translation\Language;
use InvalidArgumentException;

final class EventStatus implements SerializableInterface
{
    /**
     * @var EventStatusType
     */
    private $eventStatusType;

    /**
     * @var EventStatusReason[]
     */
    private $eventStatusReasons;

    public function __construct(EventStatusType $eventStatusType, array $eventStatusReasons)
    {
        $this->ensureTranslationsAreUnique($eventStatusReasons);
        $this->eventStatusType = $eventStatusType;
        $this->eventStatusReasons = $eventStatusReasons;
    }

    public function getEventStatusType(): EventStatusType
    {
        return $this->eventStatusType;
    }

    public function getEventStatusReasons(): array
    {
        return $this->eventStatusReasons;
    }

    public static function deserialize(array $data): EventStatus
    {
        $eventStatusReasons = [];
        foreach ($data['eventStatusReason'] as $language => $eventStatusReason) {
            $eventStatusReasons[] = new EventStatusReason(
                new Language($language),
                $eventStatusReason
            );
        }

        return new EventStatus(
            EventStatusType::fromNative($data['eventStatus']),
            $eventStatusReasons
        );
    }

    public function serialize(): array
    {
        $eventStatusReasons = [];
        foreach ($this->eventStatusReasons as $statusReason) {
            $eventStatusReasons[$statusReason->getLanguage()->getCode()] = $statusReason->getReason();
        }

        return [
            'eventStatus' => $this->eventStatusType->toNative(),
            'eventStatusReason' => $eventStatusReasons,
        ];
    }

    public function toJsonLd(): array
    {
        $eventStatusReasons = [];
        foreach ($this->eventStatusReasons as $statusReason) {
            $eventStatusReasons[$statusReason->getLanguage()->getCode()] = $statusReason->getReason();
        }

        return array_filter([
            'eventStatus' => 'https://schema.org/' . $this->eventStatusType->toNative(),
            'eventStatusReason' => $eventStatusReasons ?? null,
        ]);
    }

    /**
     * @param EventStatusReason[] $eventStatusReasons
     */
    private function ensureTranslationsAreUnique(array $eventStatusReasons): void
    {
        $languageCodes = \array_map(static function (EventStatusReason $reason) {
            return $reason->getLanguage()->getCode();
        }, $eventStatusReasons);

        if (count($languageCodes) !== count(array_unique($languageCodes))) {
            throw new InvalidArgumentException('Duplicate translations are not allowed for EventStatusReason');
        }
    }
}
