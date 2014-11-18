<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb;


class EventItemFactory
{
    /**
     * @param string $namespaceUri
     * @param string $cdbXml
     * @throws \CultureFeed_Cdb_ParseException
     * @return \CultureFeed_Cdb_Item_Event
     */
    public static function createEventFromCdbXml($namespaceUri, $cdbXml)
    {
        $udb2SimpleXml = new \SimpleXMLElement(
            $cdbXml,
            0,
            false,
            $namespaceUri
        );

        return \CultureFeed_Cdb_Item_Event::parseFromCdbXml(
            $udb2SimpleXml
        );
    }
} 
