<?php


namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;


class EventLDProjectorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function it_strips_empty_keywords_when_importing_from_udb2()
    {
        $documentRepository = $this->getMock(
          'CultuurNet\\UDB3\\Event\\ReadModel\\DocumentRepositoryInterface'
        );

        $iriGenerator = $this->getMock(
          'CultuurNet\\UDB3\\Iri\\IriGeneratorInterface'
        );

        $projector = new EventLDProjector(
          $documentRepository,
          $iriGenerator
        );

        $cdbXml = file_get_contents(__DIR__ . '/event_with_empty_keyword.cdbxml.xml');
        $event = new EventImportedFromUDB2(
          'someId',
          $cdbXml,
          'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $documentRepository->expects($this->once())
          ->method('save')
          ->with(
            $this->callback(
              function($jsonDocument){
                  $expectedKeywords = ['gent', 'Quiz', 'Gent on Files'];
                  $body = $jsonDocument->getBody();
                return count(array_diff($expectedKeywords , (array)$body->concept)) == 0;
              }
            ));

        $projector->applyEventImportedFromUDB2($event);



    }
}
 