<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer;

class OrganizerProjectedToJSONLD
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     * @param string $iri
     */
    public function __construct($id, $iri)
    {
        $this->id = (string) $id;
        $this->iri = (string) $iri;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIri()
    {
        return $this->iri;
    }
}
