<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use CultuurNet\UDB3\Language;

class TranslateTitle
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param string $id
     * @param Language $language
     * @param string $title
     */
    public function __construct($id, Language $language, $title)
    {
        $this->id = $id;
        $this->language = $language;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
