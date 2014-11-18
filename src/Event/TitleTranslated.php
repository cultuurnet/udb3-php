<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use CultuurNet\UDB3\Language;

class TitleTranslated extends EventEvent
{
    protected $language;
    protected $title;

    /**
     * @param string $id
     * @param Language $language
     * @param string $title
     */
    public function __construct($id, Language $language, $title)
    {
        parent::__construct($id);
        $this->language = $language;
        $this->title = $title;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
} 
