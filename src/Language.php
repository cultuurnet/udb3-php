<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

/**
 * Language, identified by its ISO 639-1 (2 letter) language code.
 */
class Language
{
    protected $code;

    /**
     * @param $code
     */
    public function __construct($code)
    {
        if (!preg_match('/^[a-z]{2}$/', $code)) {
            throw new \InvalidArgumentException(
                'Invalid language code: ' . $code
            );
        }
        $this->code = $code;
    }

    public function __toString()
    {
        return $this->code;
    }

    public function getCode()
    {
        return $this->code;
    }
}
