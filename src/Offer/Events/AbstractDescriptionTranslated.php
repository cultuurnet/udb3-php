<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\Language;

class AbstractDescriptionTranslated extends AbstractPropertyTranslatedEvent
{
    /**
     * @var Description
     */
    protected $description;

    /**
     * @param string $itemId
     * @param Language $language
     * @param Description $description
     */
    final public function __construct(string $itemId, Language $language, Description $description)
    {
        parent::__construct($itemId, $language);
        $this->description = $description;
    }

    /**
     * @return Description
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
            new Description($data['description'])
        );
    }
}
