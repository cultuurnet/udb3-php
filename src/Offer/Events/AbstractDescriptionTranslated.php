<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class AbstractDescriptionTranslated extends AbstractPropertyTranslatedEvent
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $itemId
     * @param Language $language
     * @param String $description
     */
    public function __construct($itemId, Language $language, String $description)
    {
        parent::__construct($itemId, $language);
        $this->description = $description;
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => $this->description,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new Language($data['language']),
            new String($data['description'])
        );
    }
}
