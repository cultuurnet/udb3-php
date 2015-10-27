<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 16:35
 */

namespace CultuurNet\UDB3;

class TooManySpecificCharactersException extends InvalidKeywordsStringException
{
    public function __construct($character)
    {
        parent::__construct(
            'Too many "' . $character .'" in your message.'
        );
    }
}
