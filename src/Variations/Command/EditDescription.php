<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Command\EditProperty;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;

class EditDescription extends EditProperty
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $editorId
     * @param Purpose $purpose
     * @param string $description
     */
    public function __construct($id, $editorId, Purpose $purpose, $description)
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
