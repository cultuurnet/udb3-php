<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations;

abstract class EditProperty
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var EditPurpose
     */
    protected $purpose;

    /**
     * @var string
     */
    protected $editorId;

    /**
     * @param $id
     * @param EditPurpose $purpose
     * @param $editorId
     */
    public function __construct($id, $editorId, EditPurpose $purpose)
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
}
