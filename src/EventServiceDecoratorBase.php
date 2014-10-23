<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


abstract class EventServiceDecoratorBase implements EventServiceInterface
{
    /**
     * @var EventServiceInterface
     */
    protected $decoratee;

    public function __construct(EventServiceInterface $decoratee) {
        $this->decoratee = $decoratee;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getEvent($id)
    {
        return $this->decoratee->getEvent($id);
    }
}
