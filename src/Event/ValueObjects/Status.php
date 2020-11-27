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
    private $type;

    /**
     * @var StatusReason[]
     */
    private $reason;

    public function __construct(StatusType $type, array $reason)
    {
        $this->ensureTranslationsAreUnique($reason);
        $this->type = $type;
        $this->reason = $reason;
    }

    public function getType(): StatusType
    {
        return $this->type;
    }

    public function getReason(): array
    {
        return $this->reason;
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
        foreach ($this->reason as $statusReason) {
            $statusReasons[$statusReason->getLanguage()->getCode()] = $statusReason->getReason();
        }

        return [
            'status' => $this->type->toNative(),
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
