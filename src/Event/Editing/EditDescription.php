<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Editing;

class EditDescription extends EditProperty
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $editorId
     * @param EditPurpose $purpose
     * @param string $description
     */
    public function __construct($id, $editorId, EditPurpose $purpose, $description)
    {
        parent::__construct($id, $editorId, $purpose);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
