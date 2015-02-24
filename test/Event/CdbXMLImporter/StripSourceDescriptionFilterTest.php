<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\StripSourceDescriptionFilter;

class StripSourceDescriptionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_strips_the_source_element_from_between_other_html_tags()
    {
        // @codingStandardsIgnoreStart
        $source_element = '<p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/fleuramour-passion-for-flowers/c2950cd0-d9e6-49f8-99fc-cffa4f004a20">UiTinVlaanderen.be</a></p>';
        // @codingStandardsIgnoreEnd
        $some_element = '<p>Some Element</p>';
        $another_element = '<p>Another Element</p>';

        $description = $some_element .
            $source_element .
            $another_element;

        $descriptionFilter = new StripSourceDescriptionFilter();
        $filteredDescription = $descriptionFilter->filter($description);
        $expectedDescription = $some_element . PHP_EOL .
            $another_element;

        $this->assertEquals($expectedDescription, $filteredDescription);
    }

    /**
     * @test
     */
    public function it_strips_the_source_element_from_between_text_and_plain_text()
    {
        // @codingStandardsIgnoreStart
        $source_element = '<p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/fleuramour-passion-for-flowers/c2950cd0-d9e6-49f8-99fc-cffa4f004a20">UiTinVlaanderen.be</a></p>';
        // @codingStandardsIgnoreEnd
        $some_element = '<p>Some Element</p>';
        $without_element = "I'm some text without an element";

        $description = $without_element .
            $source_element .
            $some_element;

        $descriptionFilter = new StripSourceDescriptionFilter();
        $filteredDescription = $descriptionFilter->filter($description);
        $expectedDescription = "<p>" . $without_element . "</p>" . PHP_EOL .
            $some_element;

        $this->assertEquals($expectedDescription, $filteredDescription);
    }

    /**
     * @test
     */
    public function it_strips_the_source_element_from_between_a_tag_and_plain_text()
    {
        // @codingStandardsIgnoreStart
        $source_element = '<p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/fleuramour-passion-for-flowers/c2950cd0-d9e6-49f8-99fc-cffa4f004a20">UiTinVlaanderen.be</a></p>';
        // @codingStandardsIgnoreEnd
        $some_element = '<p>Some Element</p>';
        $without_element = "I'm some text without an element";

        $description = $some_element .
            $source_element .
            $without_element;

        $descriptionFilter = new StripSourceDescriptionFilter();
        $filteredDescription = $descriptionFilter->filter($description);
        $expectedDescription = $some_element .
            $without_element;

        $this->assertEquals($expectedDescription, $filteredDescription);
    }

    /**
     * @test
     */
    public function it_strips_the_source_element_from_between_a_plain_text()
    {
        // @codingStandardsIgnoreStart
        $source_element = '<p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/fleuramour-passion-for-flowers/c2950cd0-d9e6-49f8-99fc-cffa4f004a20">UiTinVlaanderen.be</a></p>';
        // @codingStandardsIgnoreEnd
        $without_element = "I'm some text without an element";

        $description = $without_element .
            $source_element .
            $without_element;

        $descriptionFilter = new StripSourceDescriptionFilter();
        $filteredDescription = $descriptionFilter->filter($description);
        $expectedDescription = "<p>" . $without_element . "</p>" .
            $without_element;

        $this->assertEquals($expectedDescription, $filteredDescription);
    }
}
