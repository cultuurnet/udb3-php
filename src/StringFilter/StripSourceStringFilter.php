<?php


namespace CultuurNet\UDB3\StringFilter;

class StripSourceStringFilter implements StringFilterInterface
{

    public function filter($string)
    {
        // Don't do any filtering on empty strings as it doesn't make a difference anyway, and the loadHTML method on
        // DOMDocument would trigger a warning if an empty string would be loaded.
        if (empty($string)) {
            return $string;
        }

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
