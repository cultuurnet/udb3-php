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
            'du',
        ];

        return in_array($language->getCode(), $languages);
    }
}
