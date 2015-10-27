<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 17:04
 */

namespace CultuurNet\UDB3;

class UnequalAmountOfValuesException extends InvalidKeywordsStringException
{
    public function __construct($key1, $key2)
    {
        parent::__construct(
            'The number of values for ' . $key1 . ' do not match the number of values for ' . $key2
        );
    }
}
