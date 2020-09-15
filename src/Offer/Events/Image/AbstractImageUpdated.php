<?php

namespace CultuurNet\UDB3\Offer\Events\Image;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractImageUpdated extends AbstractEvent
{
    /**
     * The id of the media object that the new information applies to.
     * @var UUID
     */
    protected $mediaObjectId;

    /**
     * @var StringLiteral
     */
    protected $description;

    /**
     * @var StringLiteral
     */
    protected $copyrightHolder;

    /**
     * @param $itemId
     * @param UUID $mediaObjectId
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     */
    final public function __construct(
        string $itemId,
        UUID $mediaObjectId,
        StringLiteral $description,
        StringLiteral $copyrightHolder
    ) {
        parent::__construct($itemId);
        $this->mediaObjectId = $mediaObjectId;
        $this->description = $description;
        $this->copyrightHolder = $copyrightHolder;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return UUID
     */
    public function getMediaObjectId(): UUID
    {
        return $this->mediaObjectId;
    }

    /**
     * @return StringLiteral
     */
    public function getDescription(): StringLiteral
    {
        return $this->description;
    }

    /**
     * @return StringLiteral
     */
    public function getCopyrightHolder(): StringLiteral
    {
        return $this->copyrightHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return parent::serialize() +  array(
            'media_object_id' => (string) $this->mediaObjectId,
            'description' => (string) $this->description,
            'copyright_holder' => (string) $this->copyrightHolder,
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data): AbstractImageUpdated
    {
        return new static(
            $data['item_id'],
            new UUID($data['media_object_id']),
            new StringLiteral($data['description']),
            new StringLiteral($data['copyright_holder'])
        );
    }
}
