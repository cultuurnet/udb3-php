<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Purpose;

abstract class EditProperty
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Purpose
     */
    protected $purpose;

    /**
     * @var string
     */
    protected $editorId;

    /**
     * @param $id
     * @param Purpose $purpose
     * @param $editorId
     */
    public function __construct($id, $editorId, Purpose $purpose)
    {
        $this->id = $id;
        $this->editorId = $editorId;
        $this->purpose = $purpose;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
}
