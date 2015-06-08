<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

abstract class PropertyEdited extends EventEvent
{
    /**
     * @var EditPurpose
     */
    protected $purpose;

    public function __construct($id, EditPurpose $purpose)
    {
        $this->purpose = $purpose;
        parent::__construct($id);
    }

    /**
     * @return EditPurpose
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'purpose' => (string)$this->purpose,
        );
    }
}
