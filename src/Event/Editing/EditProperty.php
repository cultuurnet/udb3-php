<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Editing;

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


    public function __construct($id, $purpose)
    {
        $this->id = $id;
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
}
