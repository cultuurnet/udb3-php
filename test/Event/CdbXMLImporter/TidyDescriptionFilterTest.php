<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\TidyDescriptionFilter;

class TidyDescriptionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * Original event with this kind of broken tag
     * ID: 0c8ce12f-a9e7-4d9f-9e53-7a3a21510a4a
     * broken event XML included in even_with_broken_xml_tag.xml
     */
    public function it_escapes_broken_html_end_tags()
    {
        $element_with_valid_tag = "<p>Valid Element</p>";
        $broken_html_end_tag = "</...";

        $description = $element_with_valid_tag .
            $broken_html_end_tag .
            $element_with_valid_tag;

        $descriptionFilter = new TidyDescriptionFilter();

        $filteredDescription = $descriptionFilter->filter($description);

        $this->assertFalse(strpos($filteredDescription, $broken_html_end_tag));
    }
}
