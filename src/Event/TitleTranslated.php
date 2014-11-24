<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Language;

class TitleTranslated extends PropertyTranslated
{
    protected $title;

    /**
     * @param string $id
     * @param Language $language
     * @param string $title
     */
    public function __construct($id, Language $language, $title)
    {
        parent::__construct($id, $language);
        $this->language = $language;
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
     * @return array
     */
    public function serialize()
    {
        $value = parent::serialize() + array(
            'title' => $this->title,
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            new Language($data['language']),
            $data['title']
        );
    }
}
