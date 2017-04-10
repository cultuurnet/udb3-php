<?php

namespace CultuurNet\UDB3\ValueObject;

use CultuurNet\UDB3\Language;
use ValueObjects\StringLiteral\StringLiteral;

class MultilingualString
{
    /**
     * @var Language
     */
    private $originalLanguage;

    /**
     * @var StringLiteral
     */
    private $originalString;

    /**
     * @var StringLiteral[]
     *   Associative array with languages as keys and translations as values.
     */
    private $translations;

    public function __construct(Language $originalLanguage, StringLiteral $originalString)
    {
        $this->originalLanguage = $originalLanguage;
        $this->originalString = $originalString;
        $this->translations = [];
    }

    /**
     * @return Language
     */
    public function getOriginalLanguage()
    {
        return $this->originalLanguage;
    }

    /**
     * @return StringLiteral
     */
    public function getOriginalString()
    {
        return $this->originalString;
    }

    /**
     * @param Language $language
     * @param StringLiteral $translation
     * @return MultilingualString
     */
    public function withTranslation(Language $language, StringLiteral $translation)
    {
        if ($language->getCode() == $this->originalLanguage->getCode()) {
            throw new \InvalidArgumentException('Can not translate to original language.');
        }

        $c = clone $this;
        $c->translations[$language->getCode()] = $translation;
        return $c;
    }

    /**
     * @return StringLiteral[]
     *   Associative array with languages as keys and translations as values.
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return StringLiteral
     *   Associative array with languages as keys and translations as values.
     */
    public function getTranslationsIncludingOriginal()
    {
        return array_merge(
            [$this->originalLanguage->getCode() => $this->originalString],
            $this->translations
        );
    }

    /**
     * @param Language $preferredLanguage
     * @param Language[] ...$fallbackLanguages
     *   One or more accept languages.
     * @return StringLiteral|null
     */
    public function getStringForLanguage(Language $preferredLanguage, Language ...$fallbackLanguages)
    {
        $languages = $fallbackLanguages;
        array_unshift($languages, $preferredLanguage);

        $translations = $this->getTranslationsIncludingOriginal();

        foreach ($languages as $language) {
            if (isset($translations[$language->getCode()])) {
                return $translations[$language->getCode()];
            }
        }

        return null;
    }
}
