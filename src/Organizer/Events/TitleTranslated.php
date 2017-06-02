<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Title;

class TitleTranslated extends TitleUpdated
{
    /**
     * @var Language
     */
    private $language;

    /**
     * TitleTranslated constructor.
     * @param string $organizerId
     * @param Title $title
     * @param Language $language
     */
    public function __construct(
        $organizerId,
        Title $title,
        Language $language
    ) {
        parent::__construct($organizerId, $title);
        $this->language = $language;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + [
                'language' => $this->getLanguage()->getCode()
            ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['organizer_id'],
            new Title($data['title']),
            new Language($data['language'])
        );
    }
}