<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

class ActorCdbXmlFromSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FixedResponseSearchService
     */
    protected $search;

    /**
     * @var ActorCdbXmlFromSearchService
     */
    protected $service;

    public function setUp()
    {
        $this->search = new FixedResponseSearchService();

        $this->service = new ActorCdbXmlFromSearchService(
            $this->search
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_actor_was_not_found()
    {
        $cdbId = '061C13AC-A15F-F419-D8993D68C9E94548';

        $this->setExpectedException(
            ActorNotFoundException::class,
            "Actor with cdbid '{$cdbId}' could not be found via Search API v2."
        );

        $this->service->getCdbXmlOfActor($cdbId);
    }

    /**
     * @test
     */
    public function it_extracts_actor_from_returned_results()
    {
        $this->search->setFixedResponseFromFile(__DIR__ . '/search-results-for-actor.xml');

        $xml = $this->service->getCdbXmlOfActor('061C13AC-A15F-F419-D8993D68C9E94548');

        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/search-results-single-actor.xml',
            $xml
        );
    }
}
