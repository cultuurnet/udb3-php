<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Variations\Model\Properties\Id;

abstract class EventVariationEvent implements SerializableInterface
{
    /**
     * @var Id
     */
    private $id;

    /**
     * @param Id $id
     */
    public function __construct(Id $id)
    {
        $this->id = $id;
    }

    /**
     * @return Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'id' => (string) $this->getId(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            new Id($data['id'])
        );
    }
}
