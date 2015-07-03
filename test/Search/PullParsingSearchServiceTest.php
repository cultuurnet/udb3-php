<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Group;
use CultuurNet\Search\Parameter\Parameter;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\Search\Parameter\Rows;
use CultuurNet\Search\Parameter\Sort;
use CultuurNet\Search\Parameter\Start;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface as SAPI2;
use Guzzle\Http\Message\Response;
use ValueObjects\Number\Integer;

class PullParsingSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SAPI2|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sapi2;

    /**
     * @var PullParsingSearchService
     */
    private $search;

    public function setUp()
    {
        $this->sapi2 = $this->getMock(SAPI2::class);

        $this->search = new PullParsingSearchService(
            $this->sapi2,
            new CallableIriGenerator(
                function($item) {
                    return 'http://example.com/event/' . $item;
                }
            )
        );
    }

    /**
     * @test
     */
    public function it_processes_cdbxml_into_results()
    {
        $cdbXML = file_get_contents(__DIR__ . '/samples/search-results-leuven.xml');
        $this->sapi2->expects($this->once())
            ->method('search')
            ->willReturn(new Response(200, [], $cdbXML));

        $results = $this->search->search('city:leuven');
        $expectedItems = [
            [
                '@id' => 'http://example.com/event/ee40146e-f526-4daf-a265-28486e22298e',
            ],
            [
                '@id' => 'http://example.com/event/62a2bdf0-a9f1-4586-bc95-147682244e94',
            ],
            [
                '@id' => 'http://example.com/event/99c5a73c-b862-4133-a65f-2c878c8f3b70',
            ],
            [
                '@id' => 'http://example.com/event/cd017556-11e2-4f43-a025-41e9ff01ea0a',
            ],
            [
                '@id' => 'http://example.com/event/b7a0a43a-a539-4efe-9cfd-b4fda8b07c89',
            ],
            [
                '@id' => 'http://example.com/event/fa9e5325-2632-471b-8676-b1c34d212394',
            ],
            [
                '@id' => 'http://example.com/event/0c23b301-a681-473f-89ed-8935eee7b387',
            ],
            [
                '@id' => 'http://example.com/event/73edef75-2ca1-4461-b8c7-8ec776b66333',
            ],
            [
                '@id' => 'http://example.com/event/67afa251-8648-4919-9262-0357807f721c',
            ],
            [
                '@id' => 'http://example.com/event/5bb61842-4241-46aa-94e7-3baedc9aa7ea',
            ],
            [
                '@id' => 'http://example.com/event/32ea3ac3-5545-4b0b-8824-b80f03bf71c1',
            ],
            [
                '@id' => 'http://example.com/event/b0a628c5-802e-43a6-ae17-f1d988cc40ef',
            ],
            [
                '@id' => 'http://example.com/event/9abed1c2-143d-434c-b426-b052add6edea',
            ],
            [
                '@id' => 'http://example.com/event/2cf7fd07-c208-4730-bda4-2f9a3270b9a1',
            ],
            [
                '@id' => 'http://example.com/event/825c3b19-27c8-4f81-8886-7c2fa0999b59',
            ],
            [
                '@id' => 'http://example.com/event/a5e5d010-e450-4ddb-b899-cce91b455b1c',
            ],
            [
                '@id' => 'http://example.com/event/eabc5a0b-aec4-4d0e-b790-e3973eeb0f60',
            ],
            [
                '@id' => 'http://example.com/event/b34c362d-fdbc-4855-822c-4dcb82b0a7b3',
            ],
            [
                '@id' => 'http://example.com/event/89ab0498-00c0-4558-8b8c-d8ec10a11499',
            ],
            [
                '@id' => 'http://example.com/event/4eee2267-872c-42cf-bdd4-8da4cd718a03',
            ],
            [
                '@id' => 'http://example.com/event/c18cf7d6-f355-4f5a-ae24-1dc2f70538a1',
            ],
            [
                '@id' => 'http://example.com/event/5ef093f8-a5fc-468b-bc1b-f28c16203c2e',
            ],
            [
                '@id' => 'http://example.com/event/48d23b65-7e53-4e12-ac7b-c6d0d976ca54',
            ],
            [
                '@id' => 'http://example.com/event/a6128ae8-1af2-4100-898c-73362cba9021',
            ],
            [
                '@id' => 'http://example.com/event/46e9c658-9492-4870-9437-29cc1e64783e',
            ],
            [
                '@id' => 'http://example.com/event/ba9c50b3-e0d6-45cf-868b-389292aa7bbf',
            ],
            [
                '@id' => 'http://example.com/event/3f1614f8-2cf7-420f-bbb1-1f773720bd4e',
            ],
            [
                '@id' => 'http://example.com/event/0c4bcaaf-2873-49c4-a9a9-4d0d454b8e71',
            ],
            [
                '@id' => 'http://example.com/event/9d22ce4c-022e-499d-9702-3d7908cba046',
            ],
            [
                '@id' => 'http://example.com/event/b065b1fb-a57c-48ac-9109-e7153a1d5c21',
            ],
        ];

        $this->assertEquals($expectedItems, $results->getItems());
        $this->assertEquals(new Integer(9043), $results->getTotalItems());
    }

    /**
     * @test
     */
    public function it_passes_search_parameters_to_sapi2()
    {
        $cdbXML = file_get_contents(__DIR__ . '/samples/search-results-leuven.xml');

        $this->sapi2->expects($this->exactly(4))
            ->method('search')
            ->withConsecutive(
                [
                    $this->equalTo(
                        [
                            new Query('city:brussel'),
                            new Group(),
                            new Rows(30),
                            new Start(0),
                            new FilterQuery('type:event'),
                        ]
                    ),
                ],
                [
                    $this->equalTo(
                        [
                            new Query('city:leuven'),
                            new Group(),
                            new Rows(20),
                            new Start(0),
                            new FilterQuery('type:event'),
                        ]
                    ),
                ],
                [
                    $this->equalTo(
                        [
                            new Query('city:hasselt'),
                            new Group(),
                            new Rows(20),
                            new Start(40),
                            new FilterQuery('type:event'),
                        ]
                    ),
                ],
                [
                    $this->equalTo(
                        [
                            new Query('city:oostende'),
                            new Group(),
                            new Rows(10),
                            new Start(20),
                            new FilterQuery('type:event'),
                            new Parameter('sort', 'lastupdated desc'),
                        ]
                    ),
                ]
            )
            ->willReturn(new Response(200, [], $cdbXML));

        $this->search->search('city:brussel');

        $this->search->search('city:leuven', 20);

        $this->search->search('city:hasselt', 20, 40);

        $this->search->search('city:oostende', 10, 20, 'lastupdated desc');
    }
}
