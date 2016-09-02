<?php

namespace CultuurNet\UDB3\StringFilter;

class NewlineToBreakTagStringFilter implements StringFilterInterface
{
    /**
     * @param string $string
     * @return string
     */
    public function filter($string)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('Argument should be string, got ' . gettype($string) . ' instead.');
        }

        // nl2br() only appends <br /> after each \n but does not remove the \n
        return str_replace("\n", "<br />", $string);
    }
}
