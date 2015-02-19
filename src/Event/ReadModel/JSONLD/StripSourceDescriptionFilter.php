<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\DescriptionFilterInterface;

class StripSourceDescriptionFilter implements DescriptionFilterInterface
{

    public function filter($description)
    {
        $source_text = '/<p class="uiv-source">(.*)<\/p>/';
        return preg_replace($source_text, '', $description);
    }
}
