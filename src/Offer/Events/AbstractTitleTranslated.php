<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\StringLiteral\StringLiteral;

class AbstractTitleTranslated extends AbstractPropertyTranslatedEvent
{
    protected $title;

    /**
     * @param string $itemId
     * @param Language $language
     * @param StringLiteral $title
     */
    public function __construct($itemId, Language $language, StringLiteral $title)
    {
        parent::__construct($itemId, $language);
        $this->title = $title;
    }

    /**
     * @return StringLiteral
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
            new StringLiteral($data['title'])
        );
    }
}
