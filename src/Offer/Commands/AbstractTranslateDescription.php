<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

abstract class AbstractTranslateDescription extends AbstractTranslatePropertyCommand
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
}
