<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Search\Parameter\BooleanParameter;
use CultuurNet\Search\Parameter\Group;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;
use Guzzle\Http\Message\Response;

class EventCdbXmlFromSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchService;

    /**
     * @var EventCdbXmlFromSearchService
     */
    protected $service;

    /**
     * @var string
     */
    protected $searchEmptyResponse;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->searchService = $this->getMock(
            SearchServiceInterface::class
        );

        $this->service = new EventCdbXmlFromSearchService(
            $this->searchService
        );

        $this->searchEmptyResponse = <<<EOS
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cdb:cdbxml xmlns:cdb="http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL">
<cdb:nofrecords
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xsi:type="xs:long">0</cdb:nofrecords>
</cdb:cdbxml>
EOS;
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_event_was_not_found()
    {
        $cdbId = '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11';

        $this->setExpectedException(
            EventNotFoundException::class,
            "Event with cdbid '{$cdbId}' could not be found via Search API v2."
        );

        $response = new Response(200, [], $this->searchEmptyResponse);

        $this->searchService
            ->expects($this->once())
            ->method('search')
            ->with(
                [
                    new Query('cdbid:"' . $cdbId . '"'),
                    new Group(true),
                ]
            )
            ->willReturn($response);

        $this->service->getCdbXmlOfEvent($cdbId);
    }

    /**
     * @test
     */
    public function it_extracts_event_from_returned_results()
    {
        $response = new Response(
            200,
            [],
            file_get_contents(__DIR__ . '/search-results.xml')
        );

        $this->searchService
            ->expects($this->once())
            ->method('search')
            ->willReturn($response);

        $xml = $this->service->getCdbXmlOfEvent(
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11'
        );

        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/search-results-single-event.xml',
            $xml
        );
    }

    /**
     * @test
     */
    public function it_can_include_past_events()
    {
        $cdbId = '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11';

        $cdbXmlWithPastEvents = new EventCdbXmlFromSearchService(
            $this->searchService,
            true
        );

        $response = new Response(
            200,
            [],
            file_get_contents(__DIR__ . '/search-results.xml')
        );

        $this->searchService
            ->expects($this->once())
            ->method('search')
            ->with(
                [
                    new Query('cdbid:"' . $cdbId . '"'),
                    new Group(true),
                    new BooleanParameter('past', true),
                ]
            )
            ->willReturn($response);

        $cdbXmlWithPastEvents->getCdbXmlOfEvent($cdbId);
    }
}
