<?php

namespace CultuurNet\UDB3\Place;

use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;

class PlaceLDProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceLDProjector
     */
    protected $projector;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentRepository;

    public function setUp()
    {
        $this->documentRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->projector = new PlaceLDProjector(
            $this->documentRepository,
            $this->getMock(IriGeneratorInterface::class),
            $this->getMock(EventBusInterface::class)
        );
    }

    private function placeImportedFromUDB2($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );
        $event = new PlaceImportedFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_image_property()
    {
        $event = $this->placeImportedFromUDB2('place_without_image.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();
                return !property_exists($body, 'image');
            }));

        $this->projector->applyPlaceImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_an_image_property_when_cdbxml_has_a_photo()
    {
        $event = $this->placeImportedFromUDB2('place_with_image.cdbxml.xml');

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return $body->image === '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg';
                    }
                )
            );

        $this->projector->applyPlaceImportedFromUDB2($event);
    }

    /**
     * @return array
     */
    public function descriptionSamplesProvider()
    {
        $samples = array(
            ['place_with_short_description.cdbxml.xml', 'Korte beschrijving.'],
            ['place_with_long_description.cdbxml.xml', 'Lange beschrijving.'],
            ['place_with_short_and_long_description.cdbxml.xml', "Korte beschrijving.<br/>Lange beschrijving"]
        );

        return $samples;
    }

    /**
     * @test
     * @dataProvider descriptionSamplesProvider
     */
    public function it_adds_a_description_property_when_cdbxml_has_long_or_short_description($fileName, $expectedDescription)
    {
        $event = $this->placeImportedFromUDB2($fileName);

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) use ($expectedDescription) {
                        $body = $jsonDocument->getBody();

                        return $body->description === $expectedDescription;
                    }
                )
            );

        $this->projector->applyPlaceImportedFromUDB2($event);
    }
}
