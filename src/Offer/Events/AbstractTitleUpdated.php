<?php

namespace CultuurNet\UDB3\Offer\Events;

abstract class AbstractTitleUpdated extends AbstractEvent
{
    /**
     * The new description.
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        parent::__construct($id);
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => $this->title,
        );
    }

    /**
     * @return AbstractTitleUpdated
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], $data['title']);
    }
}
