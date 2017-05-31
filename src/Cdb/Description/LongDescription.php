<?php

namespace CultuurNet\UDB3\Cdb\Description;

use ValueObjects\StringLiteral\StringLiteral;

class LongDescription extends StringLiteral
{
    /**
     * @var LongDescriptionCdbXmlToJsonLdFilter
     */
    private static $cdbXmlToJsonLdFilter;

    /**
     * @param string $longDescriptionAsString
     * @return LongDescription
     */
    public static function fromCdbXmlToJsonLdFormat($longDescriptionAsString)
    {
        $cdbXmlToJsonLdFilter = self::getCdbXmlToJsonLdFilter();

        return new LongDescription(
            $cdbXmlToJsonLdFilter->filter($longDescriptionAsString)
        );
    }

    /**
     * @return LongDescriptionCdbXmlToJsonLdFilter
     */
    private static function getCdbXmlToJsonLdFilter()
    {
        if (!isset(self::$cdbXmlToJsonLdFilter)) {
            self::$cdbXmlToJsonLdFilter = new LongDescriptionCdbXmlToJsonLdFilter();
        }
        return self::$cdbXmlToJsonLdFilter;
    }
}
