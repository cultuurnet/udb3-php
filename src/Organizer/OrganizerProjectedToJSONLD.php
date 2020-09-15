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
     * @var string
     */
    private $iri;

    /**
     * @param string $id
     * @param string $iri
     */
    public function __construct(string $id, string $iri)
    {
        $this->id = $id;
        $this->iri = $iri;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return array
     */
    public function serialize(): array
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
    public static function deserialize(array $data): OrganizerProjectedToJSONLD
    {
        return new self($data['id'], $data['iri']);
    }
}
