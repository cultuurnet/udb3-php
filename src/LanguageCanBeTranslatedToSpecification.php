<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class LanguageCanBeTranslatedToSpecification
{
    public static function isSatisfiedBy(Language $language)
    {
        $languages = [
            'fr',
            'en',
            'de',
        ];

        return in_array($language->getCode(), $languages);
    }
}
