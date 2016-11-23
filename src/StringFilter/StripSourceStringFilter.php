<?php


namespace CultuurNet\UDB3\StringFilter;

class StripSourceStringFilter implements StringFilterInterface
{
    public function filter($string)
    {
        return preg_replace('@<p class="uiv-source">.*?</p>@', '', $string);
    }
}
