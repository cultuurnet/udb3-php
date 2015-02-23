<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\StripSourceDescriptionFilter;

class StripSourceDescriptionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_strips_the_source_element_from_a_description()
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

        $this->assertFalse(strpos($filteredDescription, $source_element));
        $this->assertNotFalse(strpos($filteredDescription, $another_element));
        $this->assertNotFalse(strpos($filteredDescription, $some_element));
    }
}
