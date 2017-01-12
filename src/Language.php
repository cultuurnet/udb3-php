<?php

namespace CultuurNet\UDB3;

class Language
{
    protected $code;

    /**
     * @param string $code
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
