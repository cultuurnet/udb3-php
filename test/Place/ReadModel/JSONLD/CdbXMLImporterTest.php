<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\CalendarFactory;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use InvalidArgumentException;

class CdbXMLImporterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CdbXMLImporter
     */
    protected $importer;

    public function setUp()
    {
        $this->importer = new CdbXMLImporter(
            new CdbXMLItemBaseImporter(),
            new CalendarFactory()
        );
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
     * @param string $fileName
     * @return \stdClass
     */
    private function createJsonPlaceFromCdbXmlWithWeekScheme($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/Calendar/' . $fileName
        );

        $actor = ActorItemFactory::createActorFromCdbXml(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL',
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
        $this->assertEquals('2013-07-18T09:04:07+02:00', $jsonPlace->availableFrom);
        $this->assertEquals(['Cultuurnet:organisation_1565'], $jsonPlace->sameAs);
    }

    /**
     * @test
     */
    public function it_should_copy_over_a_known_workflow_status()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXml('place_with_long_description.cdbxml.xml');

        $this->assertEquals('APPROVED', $jsonEvent->workflowStatus);
    }

    /**
     * @test
     */
    public function it_should_mark_a_place_as_ready_for_validation_when_importing_without_a_workflow_status()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXml('place_with_image.cdbxml.xml');

        $this->assertEquals('READY_FOR_VALIDATION', $jsonEvent->workflowStatus);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_the_workflow_status_is_unknown()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createJsonPlaceFromCdbXml('place_with_unknown_workflow_status.cdbxml.xml');
    }

    /**
     * @test
     */
    public function it_handles_place_without_week_scheme()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXmlWithWeekScheme('place_no_week_scheme.xml');
        $this->assertEquals('permanent', $jsonEvent->calendarType);
    }

    /**
     * @test
     */
    public function it_handles_place_with_week_scheme()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXmlWithWeekScheme('place_with_week_scheme.xml');
        $this->assertEquals('permanent', $jsonEvent->calendarType);
        $this->assertEquals(
            [
                [
                    'dayOfWeek' => [
                        'wednesday',
                        'thursday',
                        'friday',
                        'saturday',
                        'sunday',
                    ],
                    'opens' => '14:00',
                    'closes' => '17:00',
                ],
            ],
            $jsonEvent->openingHours
        );
    }

    /**
     * @test
     */
    public function it_handles_place_with_week_scheme_no_hours()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXmlWithWeekScheme('place_with_week_scheme_no_hours.xml');
        $this->assertEquals('permanent', $jsonEvent->calendarType);
        $this->assertEquals(
            [
                [
                    'dayOfWeek' => [
                        'saturday',
                    ],
                    'opens' => '00:00',
                    'closes' => '00:00',
                ],
            ],
            $jsonEvent->openingHours
        );
    }

    /**
     * @test
     */
    public function it_handles_place_with_week_scheme_no_closing_hours()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXmlWithWeekScheme('place_with_week_scheme_no_closing_hours.xml');
        $this->assertEquals('permanent', $jsonEvent->calendarType);
        $this->assertEquals(
            [
                [
                    'dayOfWeek' => [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                        'saturday',
                        'sunday',
                    ],
                    'opens' => '11:00',
                    'closes' => '11:00',
                ],
            ],
            $jsonEvent->openingHours
        );
    }

    /**
     * @test
     */
    public function it_handles_place_with_week_scheme_missing_closing_hours()
    {
        $jsonEvent = $this->createJsonPlaceFromCdbXmlWithWeekScheme('place_with_week_scheme_missing_closing_hours.xml');
        $this->assertEquals('permanent', $jsonEvent->calendarType);
        $this->assertEquals(
            [
                [
                    'dayOfWeek' => [
                        'wednesday',
                        'sunday',
                    ],
                    'opens' => '19:00',
                    'closes' => '23:00',
                ],
                [
                    'dayOfWeek' => [
                        'friday',
                    ],
                    'opens' => '19:00',
                    'closes' => '01:00',
                ],
                [
                    'dayOfWeek' => [
                        'saturday',
                    ],
                    'opens' => '19:00',
                    'closes' => '19:00',
                ],
            ],
            $jsonEvent->openingHours
        );
    }
}
