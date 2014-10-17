<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


class CallableIriGenerator implements IriGeneratorInterface
{
    /**
     * @var Callable
     */
    protected $callback;

    public function __construct(Callable $callback)
    {
        $this->callback = $callback;
    }

    public function iri($item)
    {
        $callback = $this->callback;
        return $callback($item);
    }
} 
