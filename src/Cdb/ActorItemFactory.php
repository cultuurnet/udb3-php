<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Cdb\ActorItemFactory.
 */

namespace CultuurNet\UDB3\Cdb;

class ActorItemFactory
{
    /**
     * @param string $namespaceUri
     * @param string $cdbXml
     * @throws \CultureFeed_Cdb_ParseException
     * @return \CultureFeed_Cdb_Item_Actor
     */
    public static function createActorFromCdbXml($namespaceUri, $cdbXml)
    {
        $udb2SimpleXml = new \SimpleXMLElement(
            $cdbXml,
            0,
            false,
            $namespaceUri
        );

        return \CultureFeed_Cdb_Item_Actor::parseFromCdbXml(
            $udb2SimpleXml
        );
    }
}
