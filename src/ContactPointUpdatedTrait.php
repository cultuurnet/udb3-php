<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ContactPointUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the ContactPointUpdated events.
 */
trait ContactPointUpdatedTrait
{

    /**
     * ContactPoint to be saved
     * @var ContactPoint
     */
    protected $contactPoint;

    public function getContactPoint()
    {
        return $this->contactPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'contactPoint' => $this->contactPoint->serialize(),
        );
    }
}
