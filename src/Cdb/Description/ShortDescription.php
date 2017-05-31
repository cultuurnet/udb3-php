<?php

namespace CultuurNet\UDB3\Cdb\Description;

use ValueObjects\StringLiteral\StringLiteral;

class ShortDescription extends StringLiteral
{
    /**
     * @var ShortDescriptionCdbXmlToJsonLdFilter
     */
    private static $cdbXmlToJsonLdFilter;

    /**
     * @param string $shortDescriptionAsString
     * @return ShortDescription
     */
    public static function fromCdbXmlToJsonLdFormat($shortDescriptionAsString)
    {
        $cdbXmlToJsonLdFilter = self::getCdbXmlToJsonLdFilter();

        return new ShortDescription(
            $cdbXmlToJsonLdFilter->filter($shortDescriptionAsString)
        );
    }

    /**
     * @return ShortDescriptionCdbXmlToJsonLdFilter
     */
    private static function getCdbXmlToJsonLdFilter()
    {
        if (!isset(self::$cdbXmlToJsonLdFilter)) {
            self::$cdbXmlToJsonLdFilter = new ShortDescriptionCdbXmlToJsonLdFilter();
        }
        return self::$cdbXmlToJsonLdFilter;
    }
}
