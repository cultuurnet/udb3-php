<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use CultuurNet\UDB3\Language;

abstract class PropertyTranslated extends EventEvent
{
    /**
     * @var Language
     */
    protected $language;

    public function __construct($id, Language $language) {
        $this->language = $language;
        parent::__construct($id);
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
} 
