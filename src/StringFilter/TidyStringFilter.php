<?php


namespace CultuurNet\UDB3\StringFilter;

class TidyStringFilter implements StringFilterInterface
{
    public function filter($description)
    {
        $config = array('show-body-only' => true);

        /** @var \tidy $tidy */
        $tidy = tidy_parse_string($description, $config, 'UTF8');
        $clean = $tidy->cleanRepair();

        return tidy_get_output($tidy);
    }
}
