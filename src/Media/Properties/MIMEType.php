<?php

namespace CultuurNet\UDB3\Media\Properties;

use ValueObjects\String\String as StringLiteral;

class MIMEType extends StringLiteral
{
    protected static $supportedSubtypes = [
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image'
    ];

    /**
     * @param string $subtypeString
     *
     * @throws UnsupportedMIMETypeException
     *
     * @return MIMEType
     */
    public static function fromSubtype($subtypeString)
    {
        $type = self::$supportedSubtypes[$subtypeString];

        if (!$type) {
            throw new UnsupportedMIMETypeException($subtypeString . ' is not supported!');
        }

        return new static($type . '/' . $subtypeString);
    }
}
