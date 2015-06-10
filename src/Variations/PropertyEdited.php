<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Event\EventEvent;

abstract class PropertyEdited extends EventEvent
{
    /**
     * @var EditPurpose
     */
    protected $purpose;

    /**
     * @var string
     */
    protected $editorId;

    public function __construct($id, $editorId, EditPurpose $purpose)
    {
        $this->purpose = $purpose;
        $this->editorId;
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
     * @return string
     */
    public function getEditorId()
    {
        return $this->editorId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'purpose' => (string)$this->purpose,
            'editor_id' => $this->editorId
        );
    }
}
