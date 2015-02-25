<?php


namespace CultuurNet\UDB3\StringFilter;

class StripSourceStringFilter implements StringFilterInterface
{

    public function filter($string)
    {
        $stringDOM = new \DOMDocument();
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
        $stringDOM->loadHTML($string);

        $selector = new \DOMXPath($stringDOM);
        foreach ($selector->query('//p[contains(attribute::class, "uiv-source")]') as $e) {
            $e->parentNode->removeChild($e);
        }

        $stringContent = "";

        $bodyNode = $stringDOM->getElementsByTagName('body')->item(0);
        foreach ($bodyNode->childNodes as $childNode) {
            $stringContent .= $stringDOM->saveHTML($childNode);
        }

        return $stringContent;
    }
}
