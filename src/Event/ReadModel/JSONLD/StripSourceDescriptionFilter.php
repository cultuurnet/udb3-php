<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\DescriptionFilterInterface;

class StripSourceDescriptionFilter implements DescriptionFilterInterface
{

    public function filter($description)
    {
        $descriptionDOM = new \DOMDocument();
        $description = mb_convert_encoding($description, 'HTML-ENTITIES', "UTF-8");
        $descriptionDOM->loadHTML($description);

        $selector = new \DOMXPath($descriptionDOM);
        foreach ($selector->query('//p[contains(attribute::class, "uiv-source")]') as $e) {
            $e->parentNode->removeChild($e);
        }

        $descriptionContent = "";

        $bodyNode = $descriptionDOM->getElementsByTagName('body')->item(0);
        foreach ($bodyNode->childNodes as $childNode) {
            $descriptionContent .= $descriptionDOM->saveHTML($childNode);
        }

        return $descriptionContent;
    }
}
