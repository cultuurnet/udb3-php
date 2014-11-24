<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Language;

abstract class TranslateProperty
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Language
     */
    protected $language;


    public function __construct($id, Language $language)
    {
        $this->id = $id;
        $this->language = $language;
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
}
