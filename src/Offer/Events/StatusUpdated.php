<?php declare(strict_types=1);

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\ValueObjects\Status;

class StatusUpdated implements SerializableInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Status
     */
    private $status;

    public function __construct(string $id, Status $status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public static function deserialize(array $data): StatusUpdated
    {
        return new StatusUpdated(
            $data['id'],
            Status::deserialize($data['status'])
        );
    }

    public function serialize(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->serialize(),
        ];
    }
}
