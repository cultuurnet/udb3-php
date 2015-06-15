<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Id;

class DeleteEventVariation
{
    /**
     * @var Id
     */
    protected $id;

    /**
     * @param Id $id
     */
    public function __construct(Id $id)
    {
        $this->id = $id;
    }

    /**
     * @return Id
     */
    public function getId()
    {
        return $this->id;
    }
}
