<?php


namespace CultuurNet\UDB3\UsedKeywordsMemory;


use CultuurNet\UDB3\Keyword;

class UsedKeywordsMemoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UsedKeywordsMemory
     */
    protected $memory;

    public function setUp()
    {
        $this->memory = new UsedKeywordsMemory();
    }

    /**
     * @test
     */
    public function it_adds_used_keywords_to_the_top_of_the_list()
    {
        $keyword = new Keyword('use-me');
        $this->memory->keywordUsed($keyword);

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals($keyword, $usedKeywords[0]);
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_maximum_ten_last_used_keywords()
    {
        $keywords = [
            new Keyword('Keyword-1'),
            new Keyword('Keyword-2'),
            new Keyword('Keyword-3'),
            new Keyword('Keyword-4'),
            new Keyword('Keyword-5'),
            new Keyword('Keyword-6'),
            new Keyword('Keyword-7'),
            new Keyword('Keyword-8'),
            new Keyword('Keyword-9'),
            new Keyword('Keyword-10'),
            new Keyword('Keyword-11'),
        ];

        foreach ($keywords as $keyword) {
            $this->memory->keywordUsed($keyword);
        }

        $usedKeywords = $this->memory->getKeywords();

        $iKeyword = 0;
        $listLength = 10;
        $reverseKeywords = array_reverse($keywords);

        $this->assertEquals(count($usedKeywords), 10);

        while ($iKeyword < $listLength) {
            $this->assertEquals(
                $reverseKeywords[$iKeyword],
                $usedKeywords[$iKeyword]
            );
            $iKeyword++;
        };
    }

    /**
     * @test
     */
    public function it_pushes_an_already_used_keyword_to_the_top_of_the_list_when_used_again(
    )
    {
        $keywords = [
            new Keyword('keyword-1'),
            new Keyword('keyword-2'),
            new Keyword('keyword-3'),
        ];

        foreach ($keywords as $keyword) {
            $this->memory->keywordUsed($keyword);
        };

        $this->memory->keywordUsed(new Keyword('keyword-2'));

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals(
            [
                new Keyword('keyword-2'),
                new Keyword('keyword-3'),
                new Keyword('keyword-1'),

            ],
            $usedKeywords
        );
    }

    /**
     * @test
     */
    public function it_only_adds_a_keyword_once()
    {
        $this->memory->keywordUsed(new Keyword('keyword-1'));
        $this->memory->keywordUsed(new Keyword('keyword-1'));

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals([new Keyword('keyword-1')], $usedKeywords);

        $this->assertCount(
            1,
            $this->memory->getUncommittedEvents()->getIterator()
        );
    }

    /**
     * @test
     */
    public function it_can_be_serialized_to_a_json_array() {
        $this->memory->keywordUsed(new Keyword('keyword-1'));
        $this->memory->keywordUsed(new Keyword('keyword-2'));

        $serializedMemory = json_encode($this->memory);

        $this->assertEquals(
            '["keyword-2","keyword-1"]',
            $serializedMemory
        );
    }
}
