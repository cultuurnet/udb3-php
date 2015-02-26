<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Description of DescriptionUpdated
 */
class DescriptionUpdated  extends EventEvent
{

    /**
     * The new description.
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => $this->description,
        );
    }

}
