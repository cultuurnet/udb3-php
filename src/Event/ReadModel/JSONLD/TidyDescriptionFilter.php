<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\ReadModel\JSONLD\DescriptionFilterInterface;

class TidyDescriptionFilter implements DescriptionFilterInterface
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
