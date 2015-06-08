<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EditDescription extends EditProperty
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
}
