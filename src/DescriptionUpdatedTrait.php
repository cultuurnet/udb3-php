<?php

/**
 * @file
 * Contains CultuurNet\UDB3\DescriptionUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the DescriptionUpdated events.
 */
trait DescriptionUpdatedTrait
{

    /**
     * The new description.
     * @var string
     */
    protected $description;

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
