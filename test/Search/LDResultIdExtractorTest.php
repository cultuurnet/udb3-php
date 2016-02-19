<?php

namespace CultuurNet\UDB3\Search;

class LDResultIdExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LDResultIdExtractor
     */
    private $extractor;

    public function setUp()
    {
        $this->extractor = new LDResultIdExtractor();
    }

    /**
     * @test
     */
    public function it_can_extract_the_id_from_a_valid_ld_search_result()
    {
        $expectedId = '449bc0c1-bfe1-4343-bd01-7197af9d4e5e';

        $result = [
            '@id' => "http://foo.bar/event/{$expectedId}",
            '@type' => 'Event',
        ];

        $actualId = $this->extractor->extract($result);

        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @test
     */
    public function it_ignores_a_traling_slash_in_the_id_link()
    {
        $expectedId = '449bc0c1-bfe1-4343-bd01-7197af9d4e5e';

        $result = [
            '@id' => "http://foo.bar/event/{$expectedId}/",
            '@type' => 'Event',
        ];

        $actualId = $this->extractor->extract($result);

        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_search_result_has_no_valid_id_key()
    {
        $result = [
            'id' => "http://foo.bar/event/1",
            '@type' => 'Event',
        ];

        $this->setExpectedException(\LogicException::class, 'Result has no @id key.');
        $this->extractor->extract($result);
    }
}
