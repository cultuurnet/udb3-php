<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Language;
use ValueObjects\StringLiteral\StringLiteral;

class AbstractDescriptionTranslated extends AbstractPropertyTranslatedEvent
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $itemId
     * @param Language $language
     * @param StringLiteral $description
     */
    public function __construct($itemId, Language $language, StringLiteral $description)
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
            'description' => $this->description->toNative(),
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
            new StringLiteral($data['description'])
        );
    }
}
