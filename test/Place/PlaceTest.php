<?php

namespace CultuurNet\UDB3\Place;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;

class PlaceTest extends AggregateRootScenarioTestCase
{
    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Place::class;
    }

    private function getCdbXML($filename)
    {
        return file_get_contents(
            __DIR__ . $filename
        );
    }

    /**
     * @test
     */
    public function it_imports_from_udb2_actors()
    {
        $cdbXml = $this->getCdbXML(
            '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
        );

        $this->scenario
            ->when(function () use ($cdbXml) {
                return Place::importFromUDB2Actor(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                );
            })
            ->then([
                new PlaceImportedFromUDB2(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }

    /**
     * @test
     */
    public function it_imports_from_udb2_events()
    {
        $cdbXml = $this->getCdbXML(
            '/event_with_cdb_externalid.cdbxml.xml'
        );

        $this->scenario
            ->when(function () use ($cdbXml) {
                return Place::importFromUDB2Event(
                    '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                );
            })
            ->then([
                new PlaceImportedFromUDB2Event(
                    '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }

    public function newPlaceProvider()
    {
        return [
            'actor' => [
                '318F2ACB-F612-6F75-0037C9C29F44087A',
                Place::importFromUDB2Actor(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $this->getCdbXML(
                        '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
                    ),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ],
            'event' => [
                '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                Place::importFromUDB2Event(
                    '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                    $this->getCdbXML(
                        '/event_with_cdb_externalid.cdbxml.xml'
                    ),
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]
        ];
    }

    /**
     * @test
     */
    public function it_applies_placeImportedFromUdb2Event_when_updating_event_cdbxml()
    {
        $cdbXml = $this->getCdbXML(
            '/event_with_cdb_externalid.cdbxml.xml'
        );

        $this->scenario
            ->withAggregateId('7914ed2d-9f28-4946-b9bd-ae8f7a4aea11')
            ->given([
                new PlaceImportedFromUDB2Event(
                    '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ])
            ->when(function (Place $place) use ($cdbXml) {
                $place->updateWithCdbXml($cdbXml, 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL');

            })
            ->then([
                new PlaceImportedFromUDB2Event(
                    '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }

    /**
     * @test
     */
    public function it_applies_placeUpdatedFromUdb2_when_updating_actor_cdbxml()
    {
        $cdbXml = $this->getCdbXML(
            '/ReadModel/JSONLD/place_with_long_description.cdbxml.xml'
        );

        $this->scenario
            ->withAggregateId('318F2ACB-F612-6F75-0037C9C29F44087A')
            ->given([
                new PlaceImportedFromUDB2(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ])
            ->when(function (Place $place) use ($cdbXml) {
                $place->updateWithCdbXml($cdbXml, 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL');
            })
            ->then([
                new PlaceUpdatedFromUDB2(
                    '318F2ACB-F612-6F75-0037C9C29F44087A',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }

    /**
     * @test
     * @dataProvider newPlaceProvider
     *
     * @param string                    $expectedId The unique id of the place element
     * @param EventSourcedAggregateRoot $place
     */
    public function it_has_an_id($expectedId, $place)
    {
        $this->assertEquals($expectedId, $place->getAggregateRootId());
    }
}
