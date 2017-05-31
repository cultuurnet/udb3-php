<?php

namespace CultuurNet\UDB3\Cdb\Description;

use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use ValueObjects\StringLiteral\StringLiteral;

class ShortDescription extends StringLiteral
{
    /**
     * @var StringFilterInterface
     */
    private static $cdbXmlToJsonLdFilter;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $filtered = self::getCdbXmlToJsonLdFilter()->filter($value);
        parent::__construct($filtered);
    }

    /**
     * @return StringFilterInterface
     */
    private static function getCdbXmlToJsonLdFilter()
    {
        if (!isset(self::$cdbXmlToJsonLdFilter)) {
            self::$cdbXmlToJsonLdFilter = new CdbXmlShortDescriptionToJsonLdFilter();
        }
        return self::$cdbXmlToJsonLdFilter;
    }
}
