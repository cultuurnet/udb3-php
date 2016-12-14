<?php

namespace CultuurNet\UDB3\Media\Properties;

use InvalidArgumentException;
use ValueObjects\String\String;

class MIMEType extends String
{
    protected static $supportedSubtypes = [
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image'
    ];

    /**
     * @param string $subtypeString
     *
     * @return MIMEType
     */
    public static function fromSubtype($subtypeString)
    {
        $type = self::$supportedSubtypes[$subtypeString];

        if (!$type) {
            throw new InvalidArgumentException($subtypeString . ' is not supported!');
        }

        return new static($type . '/' . $subtypeString);
    }
}
