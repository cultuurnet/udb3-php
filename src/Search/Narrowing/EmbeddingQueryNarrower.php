<?php

namespace CultuurNet\UDB3\Search\Narrowing;

/**
 * Narrows down a query by embedding it into a bigger one that contains further restrictions.
 */
abstract class EmbeddingQueryNarrower implements QueryNarrowerInterface
{
    /**
     * @var string
     */
    private $query;

    /**
     * EmbeddingQueryNarrower constructor.
     *
     * @param string $query
     *   The big query containing further restrictions. Should contain a placeholder %s at the location where the
     *   unspecific query needs to be embedded.
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function narrow(string $query): string
    {
        return sprintf($this->query, $query);
    }
}
