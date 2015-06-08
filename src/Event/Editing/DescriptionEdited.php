<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Editing;

class DescriptionEdited extends PropertyEdited
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param EditPurpose $purpose
     * @param string $description
     */
    public function __construct($id, EditPurpose $purpose, $description)
    {
        parent::__construct($id, $purpose);
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
            'description' => $this->description,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            new EditPurpose($data['purpose']),
            $data['description']
        );
    }
}
