<?php

namespace CultuurNet\UDB3\Cdb\Description;

use CultuurNet\UDB3\StringFilter\BreakTagToNewlineStringFilter;
use CultuurNet\UDB3\StringFilter\CombinedStringFilter;
use CultuurNet\UDB3\StringFilter\ConsecutiveBlockOfTextStringFilter;
use CultuurNet\UDB3\StringFilter\NewlineToBreakTagStringFilter;
use CultuurNet\UDB3\StringFilter\StripSourceStringFilter;
use CultuurNet\UDB3\StringFilter\StripSurroundingSpaceStringFilter;

class JsonLdDescriptionToCdbXmlLongDescriptionFilter extends CombinedStringFilter
{
    public function __construct()
    {
        // Convert any \n to a <br> tag.
        $nl2brFilter = new NewlineToBreakTagStringFilter();

        // Make sure \n is replaced with <br> and not <br />.
        $nl2brFilter->closeTag(false);

        $this->addFilter($nl2brFilter);
    }
}
