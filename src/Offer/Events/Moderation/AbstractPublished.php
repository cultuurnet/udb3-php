<?php

namespace CultuurNet\UDB3\Offer\Events\Moderation;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use DateTimeInterface;

abstract class AbstractPublished extends AbstractEvent
{
    /**
     * @var DateTimeInterface
     */
    private $publicationDate;

    /**
     * AbstractPublish constructor.
     * @param string $itemId
     * @param \DateTimeInterface
     */
    final public function __construct(string $itemId, DateTimeInterface $publicationDate)
    {
        parent::__construct($itemId);

        $this->publicationDate = $publicationDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getPublicationDate(): DateTimeInterface
    {
        return $this->publicationDate;
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + [
            'publication_date' => $this->publicationDate->format(\DateTime::ATOM),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): AbstractPublished
    {
        return new static(
            $data['item_id'],
            \DateTime::createFromFormat(\DateTime::ATOM, $data['publication_date'])
        );
    }
}
