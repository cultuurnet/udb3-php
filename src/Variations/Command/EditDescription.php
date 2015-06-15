<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;

class EditDescription extends EditProperty
{
    /**
     * @var Description
     */
    protected $description;

    /**
     * @param Id $id
     * @param Description $description
     */
    public function __construct(Id $id, Description $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }
}
