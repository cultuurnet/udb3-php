<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractTranslateDescription extends AbstractTranslatePropertyCommand
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
     * @return StringLiteral
     */
    public function getDescription()
    {
        return $this->description;
    }
}
