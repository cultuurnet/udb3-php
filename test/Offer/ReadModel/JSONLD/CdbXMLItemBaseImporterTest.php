<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use CultureFeed_Cdb_Data_EventDetail;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Iri\CallableIriGenerator;

class CdbXMLItemBaseImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CdbXMLItemBaseImporter
     */
    private $importer;

    public function setUp()
    {
        $mediaIriGenerator = new CallableIriGenerator(function ($file) {
            return 'http://du.de/media/87e8b421-5e87-4fae-bb3b-2c9119852a11';
        });

        $this->importer = new CdbXMLItemBaseImporter($mediaIriGenerator);
    }

    /**
     * @test
     */
    public function it_should_project_udb2_media_files_as_media_objects()
    {
        $jsonData = (object)[];
        $expectedJsonData = (object)[
            'mediaObject' => [
                [
                    '@id' => 'http://du.de/media/87e8b421-5e87-4fae-bb3b-2c9119852a11',
                    '@type' => 'schema:ImageObject',
                    'contentUrl' => '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
                    'thumbnailUrl' => '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
                    'copyrightHolder' => "'Bekend met Gent' - quiz",
                ]
            ]
        ];

        $this->importer->importMedia(
            $this->getEventDetailsFromCDBXMLFile('event_with_photo.cdbxml.xml'),
            $jsonData
        );

        $this->assertEquals($expectedJsonData, $jsonData);
    }

    /**
     * @test
     */
    public function it_should_project_the_main_udb2_picture_as_image()
    {
        $jsonData = (object)[];
        $expectedJsonData = (object)[
            'image' => '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg'
        ];

        $this->importer->importPicture(
            $this->getEventDetailsFromCDBXMLFile('event_with_photo.cdbxml.xml'),
            $jsonData
        );

        $this->assertEquals($expectedJsonData, $jsonData);
    }

    /**
     * @param string $fileName
     * @return CultureFeed_Cdb_Data_EventDetail
     */
    private function getEventDetailsFromCDBXMLFile($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/../../../Event/samples/' . $fileName
        );

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL',
            $cdbXml
        );

        return $udb2Event->getDetails()->current();
    }
}
