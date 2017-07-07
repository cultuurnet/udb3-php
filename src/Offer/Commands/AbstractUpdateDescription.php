<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;

abstract class AbstractUpdateDescription extends AbstractCommand
{
    /**
     * Description to be added.
     * @var string
     */
    protected $description;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @param string $itemId
     * @param string $description
     * @param Language $language
     */
    public function __construct($itemId, $description, Language $language)
    {
        parent::__construct($itemId);
        $this->description = $description;
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
