<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;

abstract class PropertyEdited extends EventEvent
{
    /**
     * @var Purpose
     */
    protected $purpose;

    /**
     * @var string
     */
    protected $editorId;

    public function __construct($id, $editorId, Purpose $purpose)
    {
        $this->purpose = $purpose;
        $this->editorId;
        parent::__construct($id);
    }

    /**
     * @return Purpose
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
