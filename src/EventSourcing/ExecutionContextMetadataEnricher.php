<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing;

use Broadway\Domain\Metadata;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnricherInterface;
use CultuurNet\UDB3\CommandHandling\ContextAwareInterface;
use CultuurNet\UDB3\CommandHandling\ContextAwareTrait;

class ExecutionContextMetadataEnricher implements MetadataEnricherInterface, ContextAwareInterface
{
    use ContextAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function enrich(Metadata $metadata)
    {
        if ($this->metadata) {
            return $metadata->merge($this->metadata);
        } else {
            return $metadata;
        }
    }
}
