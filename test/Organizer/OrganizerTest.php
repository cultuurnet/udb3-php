<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 07/01/16
 * Time: 11:48
 */

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;

class OrganizerTest extends AggregateRootScenarioTestCase
{
    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Organizer::class;
    }

    private function getCdbXML($filename) {
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
            '/organizer_with_email.cdbxml.xml'
        );

        $this->scenario
            ->when(function () use ($cdbXml) {
                return Organizer::importFromUDB2(
                    '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                );
            })
            ->then([
                new OrganizerImportedFromUDB2(
                    '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }
}