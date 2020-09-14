<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Title;

abstract class AbstractTitleTranslated extends AbstractPropertyTranslatedEvent
{
    protected $title;

    /**
     * @param string $itemId
     * @param Language $language
     * @param Title $title
     */
    final public function __construct(string $itemId, Language $language, Title $title)
    {
        parent::__construct($itemId, $language);
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
     * @return array
     */
    public function serialize()
    {
        $value = parent::serialize() + array(
                'title' => $this->title->toNative(),
            );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new Language($data['language']),
            new Title($data['title'])
        );
    }
}
