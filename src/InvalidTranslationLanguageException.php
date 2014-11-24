<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class InvalidTranslationLanguageException extends \RuntimeException
{
    public function __construct(Language $language)
    {
        parent::__construct(
            sprintf(
                'Can not translate to language %s',
                $language->getCode()
            )
        );
    }
}
