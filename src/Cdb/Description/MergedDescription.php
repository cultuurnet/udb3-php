<?php

namespace CultuurNet\UDB3\Cdb\Description;

use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use ValueObjects\StringLiteral\StringLiteral;

class MergedDescription extends StringLiteral
{
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
     * @return MergedDescription
     * @throws \InvalidArgumentException
     *   When there is no long and no short description on the event detail.
     */
    public static function fromCdbEventDetail(\CultureFeed_Cdb_Data_EventDetail $eventDetail)
    {
        return self::fromCdbEventOrActorDetails($eventDetail);
    }

    /**
     * @param \CultureFeed_Cdb_Data_ActorDetail $actorDetail
     * @return MergedDescription
     * @throws \InvalidArgumentException
     *   When there is no long and no short description on the actor detail.
     */
    public static function fromCdbActorDetail(\CultureFeed_Cdb_Data_ActorDetail $actorDetail)
    {
        return self::fromCdbEventOrActorDetails($actorDetail);
    }

    /**
     * @param \CultureFeed_Cdb_Data_EventDetail|\CultureFeed_Cdb_Data_ActorDetail $detail
     * @return MergedDescription
     * @throws \InvalidArgumentException
     */
    private static function fromCdbEventOrActorDetails($detail)
    {
        $longDescription = $detail->getLongDescription();
        if ($longDescription) {
            $longDescription = LongDescription::fromCdbXmlToJsonLdFormat($longDescription);
        }

        $shortDescription = $detail->getShortDescription();
        if ($shortDescription) {
            $shortDescription = ShortDescription::fromCdbXmlToJsonLdFormat($shortDescription);
        }

        if ($longDescription && $shortDescription) {
            return MergedDescription::merge($shortDescription, $longDescription);
        }

        if ($longDescription) {
            return new MergedDescription($longDescription->toNative());
        }

        if ($shortDescription) {
            return new MergedDescription($shortDescription->toNative());
        }

        throw new \InvalidArgumentException(
            'Could not create MergedDescription object from given ' . get_class($detail) . '.'
        );
    }

    /**
     * @param ShortDescription $shortDescription
     * @param LongDescription $longDescription
     * @return MergedDescription $longDescription
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
            return new MergedDescription($longAsString);
        } else {
            return new MergedDescription($shortAsString . PHP_EOL . PHP_EOL . $longAsString);
        }
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
