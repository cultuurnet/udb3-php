<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

abstract class AbstractTranslateTitle extends AbstractTranslatePropertyCommand
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @param string $itemId
     * @param Language $language
     * @param string $title
     */
    public function __construct($itemId, Language $language, String $title)
    {
        parent::__construct($itemId, $language);
        $this->title = $title;
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }
}
