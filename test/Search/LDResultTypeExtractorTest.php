<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\OfferType;

class LDResultTypeExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LDResultTypeExtractor
     */
    private $extractor;

    public function setUp()
    {
        $this->extractor = new LDResultTypeExtractor();
    }

    /**
     * @test
     */
    public function it_can_extract_the_type_from_a_valid_ld_search_result()
    {
        $expectedType = OfferType::PLACE();

        $result = [
            '@id' => "http://foo.bar/place/1",
            '@type' => 'Place',
        ];

        $actualType = $this->extractor->extract($result);

        $this->assertEquals($expectedType, $actualType);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_the_provided_result_is_not_an_array()
    {
        $result = new \stdClass();
        $this->setExpectedException(\InvalidArgumentException::class, 'Result should be an array.');
        $this->extractor->extract($result);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_search_result_has_no_valid_type_key()
    {
        $result = [
            '@id' => "http://foo.bar/event/1",
            'type' => 'Event',
        ];

        $this->setExpectedException(\LogicException::class, 'Result has no @type key.');
        $this->extractor->extract($result);
    }
}
