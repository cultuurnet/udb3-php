<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;

abstract class AbstractTranslatePropertyCommand
{
    /**
     * @var string
     */
    protected $itemId;
    /**
     * @var Language
     */
    protected $language;

    public function __construct($itemId, Language $language)
    {
        $this->itemId = $itemId;
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
