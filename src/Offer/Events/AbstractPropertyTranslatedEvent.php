<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;

abstract class AbstractPropertyTranslatedEvent extends AbstractEvent implements SerializableInterface
{
    /**
     * @var Language
     */
    protected $language;

    public function __construct($itemId, Language $language)
    {
        $this->language = $language;
        parent::__construct($itemId);
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'language' => (string)$this->language->getCode(),
        );
    }
}
