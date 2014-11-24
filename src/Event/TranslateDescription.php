<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Language;

class TranslateDescription extends TranslateProperty
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param Language $language
     * @param string $description
     */
    public function __construct($id, Language $language, $description)
    {
        parent::__construct($id, $language);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
