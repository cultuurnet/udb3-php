<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing;

use Broadway\Domain\Metadata;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnricherInterface;
use CultuurNet\UDB3\CommandHandling\ContextAwareInterface;

class ExecutionContextMetadataEnricher implements MetadataEnricherInterface, ContextAwareInterface
{
    protected $metadata;

    /**
     * @param Metadata $metadata
     */
    public function setContext(Metadata $metadata = null)
    {
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function enrich(Metadata $metadata)
    {
        if ($this->metadata) {
            return $metadata->merge($this->metadata);
        }
        else {
            return $metadata;
        }
    }

}
