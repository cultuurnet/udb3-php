<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Iri;

/**
 * IRI generator implementation that delegates the actual task to a PHP
 * callable/closure.
 */
class CallableIriGenerator implements IriGeneratorInterface
{
    /**
     * @var Callable
     */
    protected $callback;

    /**
     * Constructs a new CallableIriGenerator.
     *
     * @param callable $callback
     *   The callback to delegate the generation of IRIs to.
     */
    public function __construct(Callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function iri($item)
    {
        $callback = $this->callback;
        return $callback($item);
    }
} 
