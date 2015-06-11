<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;

class DescriptionEdited extends EventVariationEvent
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param Id $id
     * @param Description $description
     */
    public function __construct(Id $id, Description $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => (string) $this->description,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            new Id($data['id']),
            new Description($data['description'])
        );
    }
}
