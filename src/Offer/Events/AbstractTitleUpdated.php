<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Title;

abstract class AbstractTitleUpdated extends AbstractEvent
{
    /**
     * The new title.
     * @var Title
     */
    protected $title;

    /**
     * @param string $id
     * @param Title $title
     */
    final public function __construct(string $id, Title $title)
    {
        parent::__construct($id);
        $this->title = $title;
    }

    /**
     * @return Title
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
            'title' => (string) $this->title,
        );
    }

    /**
     * @return AbstractTitleUpdated
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], new Title($data['title']));
    }
}
