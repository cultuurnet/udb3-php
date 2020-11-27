<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Translation\Language;
use InvalidArgumentException;

final class Status implements SerializableInterface
{
    /**
     * @var StatusType
     */
    private $statusType;

    /**
     * @var EventStatusReason[]
     */
    private $eventStatusReasons;

    public function __construct(StatusType $statusType, array $eventStatusReasons)
    {
        $this->ensureTranslationsAreUnique($eventStatusReasons);
        $this->statusType = $statusType;
        $this->eventStatusReasons = $eventStatusReasons;
    }

    public function getStatusType(): StatusType
    {
        return $this->statusType;
    }

    public function getEventStatusReasons(): array
    {
        return $this->eventStatusReasons;
    }

    public static function deserialize(array $data): Status
    {
        $eventStatusReasons = [];
        foreach ($data['eventStatusReason'] as $language => $eventStatusReason) {
            $eventStatusReasons[] = new EventStatusReason(
                new Language($language),
                $eventStatusReason
            );
        }

        return new Status(
            StatusType::fromNative($data['eventStatus']),
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
            'eventStatus' => $this->statusType->toNative(),
            'eventStatusReason' => $eventStatusReasons,
        ];
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
