<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class AbstractTitleTranslated extends AbstractPropertyTranslatedEvent
{
    protected $title;

    /**
     * @param string $itemId
     * @param Language $language
     * @param String $title
     */
    public function __construct($itemId, Language $language, String $title)
    {
        parent::__construct($itemId, $language);
        $this->title = $title;
    }

    /**
     * @return String
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
            new String($data['title'])
        );
    }
}
