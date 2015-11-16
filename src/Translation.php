<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 09/11/15
 * Time: 14:31
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

class Translation
{
    /**
     * @var Language
     */
    protected $language;

    /**
     * @var String
     */
    protected $title;

    /**
     * @var String
     */
    protected $shortDescription;

    /**
     * @var String
     */
    protected $longDescription;

    /**
     * Translation constructor.
     * @param Language $language
     * @param String|null $title
     * @param String|null $shortDescription
     * @param String|null $longDescription
     */
    public function __construct(
        Language $language,
        String $title = null,
        String $shortDescription = null,
        String $longDescription = null
    ) {
        $this->language = $language;
        $this->title = $title;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
    }

    /**
     * Merge new translations with an existing translation.
     * @param Translation $newTranslation
     * @return Translation
     */
    public function mergeTranslation(Translation $newTranslation)
    {
        if ($newTranslation->getTitle() !== null) {
            $newTitle = $newTranslation->getTitle();
        } else {
            $newTitle = $this->title;
        }

        if ($newTranslation->getShortDescription() !== null) {
            $newShortDescription = $newTranslation->getShortDescription();
        } else {
            $newShortDescription = $this->shortDescription;
        }

        if ($newTranslation->getLongDescription() !== null) {
            $newLongDescription = $newTranslation->getLongDescription();
        } else {
            $newLongDescription = $this->longDescription;
        }

        $translation = new Translation(
            $this->language,
            $newTitle,
            $newShortDescription,
            $newLongDescription
        );

        return $translation;
    }

    /**
     * @return \CultuurNet\UDB3\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return String
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return String
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
}
