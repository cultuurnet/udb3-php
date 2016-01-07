<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 07/01/16
 * Time: 11:48
 */

namespace CultuurNet\UDB3\Place;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;

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
                return Place::importFromUDB2(
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
}
