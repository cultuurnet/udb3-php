<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 16:51
 */

namespace CultuurNet\UDB3;

class KeyNotFoundException extends InvalidKeywordsStringException
{
    public function __construct($expectedKey, $keyFound = null)
    {
        $errorMessage = 'Expected ' . $expectedKey;

        if ($keyFound != null) {
            $errorMessage .= ', found ' . $keyFound;
        }

        parent::__construct($errorMessage);
    }
}
