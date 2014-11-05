<?php


namespace CultuurNet\UDB3\UsedKeywordsMemory;


class UsedKeywordsMemoryTest extends \PHPUnit_Framework_TestCase{

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
    public function it_adds_used_keywords_to_the_top_of_the_list ()
    {
        $keyword = 'use-me';
        $this->memory->keywordUsed($keyword);

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals($keyword, $usedKeywords[0]);
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_maximum_ten_last_used_keywords ()
    {
        $keywords = [
          'Keyword-1',
          'Keyword-2',
          'Keyword-3',
          'Keyword-4',
          'Keyword-5',
          'Keyword-6',
          'Keyword-7',
          'Keyword-8',
          'Keyword-9',
          'Keyword-10',
          'Keyword-11',
        ];

        foreach($keywords as $keyword) {
            $this->memory->keywordUsed($keyword);
        }

        $usedKeywords = $this->memory->getKeywords();

        $iKeyword = 0;
        $listLength = 10;
        $reverseKeywords = array_reverse($keywords);

        $this->assertEquals(count($usedKeywords), 10);

        while($iKeyword < $listLength) {
            $this->assertEquals($reverseKeywords[$iKeyword], $usedKeywords[$iKeyword]);
            $iKeyword++;
        };
    }

    /**
     * @test
     */
    public function it_pushes_an_already_used_keyword_to_the_top_of_the_list_when_used_again ()
    {
        $keywords = [
          'keyword-1',
          'keyword-2',
          'keyword-3'
        ];

        foreach($keywords as $keyword) {
            $this->memory->keywordUsed($keyword);
        };

        $this->memory->keywordUsed('keyword-2');

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals([
            'keyword-2',
            'keyword-3',
            'keyword-1'
        ], $usedKeywords);
    }

    /**
     * @test
     */
    public function it_only_adds_a_keyword_once ()
    {
        $this->memory->keywordUsed('keyword-1');
        $this->memory->keywordUsed('keyword-1');

        $usedKeywords = $this->memory->getKeywords();

        $this->assertEquals(['keyword-1'], $usedKeywords);

        $this->assertCount(
            1,
            $this->memory->getUncommittedEvents()->getIterator()
        );
    }
}
