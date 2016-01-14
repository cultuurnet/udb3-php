<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;

class CdbXMLImporterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CdbXMLImporter
     */
    protected $importer;

    public function setUp()
    {
        $this->importer = new CdbXMLImporter(new CdbXMLItemBaseImporter());
        date_default_timezone_set('Europe/Brussels');
    }

    /**
     * @param string $fileName
     * @return \stdClass
     */
    private function createJsonPlaceFromCdbXml($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        $actor = ActorItemFactory::createActorFromCdbXml(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL',
            $cdbXml
        );

        $jsonPlace = $this->importer->documentWithCdbXML(
            new \stdClass(),
            $actor
        );

        return $jsonPlace;
    }

    /**
     * @test
     */
    public function it_imports_the_publication_info()
    {
        $jsonPlace = $this->createJsonPlaceFromCdbXml('place_with_long_description.cdbxml.xml');

        $this->assertEquals('2013-07-18T09:04:37+02:00', $jsonPlace->modified);
        $this->assertEquals('cultuurnet001', $jsonPlace->creator);
        $this->assertEquals('Invoerders Algemeen ', $jsonPlace->publisher);
        $this->assertEquals('2013-07-18T09:04:07+02:00', $jsonPlace->available);
        $this->assertEquals(['Cultuurnet:organisation_1565'], $jsonPlace->sameAs);
    }
}
