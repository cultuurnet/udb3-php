<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;

use CultureFeed_Cdb_Data_Keyword;
use CultureFeed_Cdb_Item_Event;
use CultureFeed_Cdb_ParseException;
use SimpleXMLElement;

class EventItemFactory
{
    /**
     * @param string $namespaceUri
     * @param string $cdbXml
     * @throws CultureFeed_Cdb_ParseException
     * @return CultureFeed_Cdb_Item_Event
     */
    public static function createEventFromCdbXml($namespaceUri, $cdbXml)
    {
        $udb2SimpleXml = new SimpleXMLElement(
            $cdbXml,
            0,
            false,
            $namespaceUri
        );

        $event = CultureFeed_Cdb_Item_Event::parseFromCdbXml($udb2SimpleXml);

        if (self::isEventOlderThanSplitKeywordFix($event)) {
            $event = self::splitKeywordTagOnSemiColon($event);
        }

        return $event;
    }

    /**
     * UDB2 contained a bug that allowed for a keyword to have a semicolon.
     * @param CultureFeed_Cdb_Item_Event $event
     * @return CultureFeed_Cdb_Item_Event
     */
    private static function splitKeywordTagOnSemiColon(
        CultureFeed_Cdb_Item_Event $event
    ) {
        $event = clone $event;

        /**
         * @var CultureFeed_Cdb_Data_Keyword[] $keywords
         */
        $keywords = $event->getKeywords(true);

        foreach ($keywords as $keyword) {
            $individualKeywords = explode(';', $keyword->getValue());

            if (count($individualKeywords) > 1) {
                $event->deleteKeyword($keyword);

                foreach ($individualKeywords as $individualKeyword) {
                    $newKeyword = new CultureFeed_Cdb_Data_Keyword(
                        trim($individualKeyword),
                        $keyword->isVisible()
                    );
                    $event->addKeyword($newKeyword);
                }
            }
        }

        return $event;
    }

    /**
     * @param CultureFeed_Cdb_Item_Event $event
     * @return bool
     */
    private static function isEventOlderThanSplitKeywordFix(
        CultureFeed_Cdb_Item_Event $event
    ) {
        return $event->getLastUpdated() < '2016-03-10T00:00:00';
    }
}
