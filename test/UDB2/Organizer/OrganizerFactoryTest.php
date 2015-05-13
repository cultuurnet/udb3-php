<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainMessageInterface;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Organizer;

class OrganizerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_an_organizer_entity_based_on_cdbxml()
    {
        $factory = new OrganizerFactory();

        $id = '404EE8DE-E828-9C07-FE7D12DC4EB24480';
        $cdbXml = file_get_contents(__DIR__ . '/samples/organizer.xml');
        $cdbXmlNamespaceUri = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

        $organizer = $factory->createFromCdbXml(
            $id,
            $cdbXml,
            $cdbXmlNamespaceUri
        );

        $this->assertInstanceOf(Organizer::class, $organizer);
        $this->assertEvents(
            [
                new OrganizerImportedFromUDB2(
                    $id,
                    $cdbXml,
                    $cdbXmlNamespaceUri
                ),
            ],
            $organizer
        );
    }

    private function assertEvents(array $expectedEvents, AggregateRoot $organizer)
    {
        $domainMessages = iterator_to_array(
            $organizer->getUncommittedEvents()->getIterator()
        );

        $payloads = $domainMessages;
        array_walk($payloads, function (DomainMessageInterface &$item) {
            $item = $item->getPayload();
        });

        $this->assertEquals(
            $expectedEvents,
            $payloads
        );
    }
}
