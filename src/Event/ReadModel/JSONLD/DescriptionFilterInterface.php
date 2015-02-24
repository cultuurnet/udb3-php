<?php


namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

/**
 * Interface for filtering the description of a json-ld event.
 */
interface DescriptionFilterInterface
{

    /**
     * @param string $description
     * @return string
     */
    public function filter($description);
}
