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
     * @var StatusReason[]
     */
    private $statusReason;

    public function __construct(StatusType $statusType, array $statusReason)
    {
        $this->ensureTranslationsAreUnique($statusReason);
        $this->statusType = $statusType;
        $this->statusReason = $statusReason;
    }

    public function getStatusType(): StatusType
    {
        return $this->statusType;
    }

    public function getStatusReason(): array
    {
        return $this->statusReason;
    }

    public static function deserialize(array $data): Status
    {
        $statusReasons = [];
        foreach ($data['statusReason'] as $language => $statusReason) {
            $statusReasons[] = new StatusReason(
                new Language($language),
                $statusReason
            );
        }

        return new Status(
            StatusType::fromNative($data['status']),
            $statusReasons
        );
    }

    public function serialize(): array
    {
        $statusReasons = [];
        foreach ($this->statusReason as $statusReason) {
            $statusReasons[$statusReason->getLanguage()->getCode()] = $statusReason->getReason();
        }

        return [
            'status' => $this->statusType->toNative(),
            'statusReason' => $statusReasons,
        ];
    }

    /**
     * @param StatusReason[] $statusReason
     */
    private function ensureTranslationsAreUnique(array $statusReason): void
    {
        $languageCodes = \array_map(static function (StatusReason $reason) {
            return $reason->getLanguage()->getCode();
        }, $statusReason);

        if (count($languageCodes) !== count(array_unique($languageCodes))) {
            throw new InvalidArgumentException('Duplicate translations are not allowed for StatusReason');
        }
    }
}
