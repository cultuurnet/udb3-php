<?php

namespace CultuurNet\UDB3\Cdb\Description;

use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use ValueObjects\StringLiteral\StringLiteral;

class LongDescription extends StringLiteral
{
    /**
     * @var StringFilterInterface
     */
    private static $cdbXmlToJsonLdFilter;

    /**
     * @var StringFilterInterface
     */
    private static $shortDescriptionUDB2FormattingFilter;

    /**
     * @var StringFilterInterface
     */
    private static $shortDescriptionUDB3FormattingFilter;

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail $eventDetail
     * @return LongDescription
     * @throws \InvalidArgumentException
     *   When there is no long and no short description on the event detail.
     */
    public static function fromCdbEventDetail(\CultureFeed_Cdb_Data_EventDetail $eventDetail)
    {
        $longDescription = $eventDetail->getLongDescription();
        if ($longDescription) {
            $longDescription = LongDescription::fromCdbXmlToJsonLdFormat($longDescription);
        }

        $shortDescription = $eventDetail->getShortDescription();
        if ($shortDescription) {
            $shortDescription = ShortDescription::fromCdbXmlToJsonLdFormat($shortDescription);
        }

        if ($longDescription && $shortDescription) {
            return LongDescription::merge($shortDescription, $longDescription);
        }

        if ($longDescription) {
            return $longDescription;
        }

        if ($shortDescription) {
            return new LongDescription($shortDescription->toNative());
        }

        throw new \InvalidArgumentException(
            'Could not create LongDescription object from given \CultureFeed_Cdb_Data_EventDetail.'
        );
    }

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
     * @param ShortDescription $shortDescription
     * @param LongDescription $longDescription
     * @return LongDescription $longDescription
     */
    public static function merge(ShortDescription $shortDescription, LongDescription $longDescription)
    {
        $shortAsString = $shortDescription->toNative();
        $longAsString = $longDescription->toNative();

        $longFormattedAsUdb2Short = self::getShortDescriptionUDB2FormattingFilter()->filter($longAsString);
        $longFormattedAsUdb3Short = self::getShortDescriptionUDB3FormattingFilter()->filter($longAsString);

        $udb2Comparison = strncmp($longFormattedAsUdb2Short, $shortAsString, mb_strlen($shortAsString));
        $udb3Comparison = strncmp($longFormattedAsUdb3Short, $shortAsString, mb_strlen($shortAsString));

        $shortIncludedInLong = $udb2Comparison === 0 || $udb3Comparison === 0;

        if ($shortIncludedInLong) {
            return $longDescription;
        } else {
            return new LongDescription($shortAsString . PHP_EOL . PHP_EOL . $longAsString);
        }
    }

    /**
     * @return StringFilterInterface
     */
    private static function getCdbXmlToJsonLdFilter()
    {
        if (!isset(self::$cdbXmlToJsonLdFilter)) {
            self::$cdbXmlToJsonLdFilter = new CdbXmlLongDescriptionToJsonLdFilter();
        }
        return self::$cdbXmlToJsonLdFilter;
    }

    /**
     * @return StringFilterInterface
     */
    private static function getShortDescriptionUDB2FormattingFilter()
    {
        if (!isset(self::$shortDescriptionUDB2FormattingFilter)) {
            self::$shortDescriptionUDB2FormattingFilter = new ShortDescriptionUDB2FormattingFilter();
        }
        return self::$shortDescriptionUDB2FormattingFilter;
    }

    /**
     * @return StringFilterInterface
     */
    private static function getShortDescriptionUDB3FormattingFilter()
    {
        if (!isset(self::$shortDescriptionUDB3FormattingFilter)) {
            self::$shortDescriptionUDB3FormattingFilter = new ShortDescriptionUDB3FormattingFilter();
        }
        return self::$shortDescriptionUDB3FormattingFilter;
    }
}
