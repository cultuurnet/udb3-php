<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer;

use Broadway\Serializer\SerializableInterface;

class OrganizerProjectedToJSONLD implements SerializableInterface
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

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'iri' => $this->getIri(),
        ];
    }

    /**
     * @param array $data
     * @return OrganizerProjectedToJSONLD
     */
    public static function deserialize(array $data)
    {
        return new self($data['id'], $data['iri']);
    }
}
