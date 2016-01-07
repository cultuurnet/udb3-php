<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Actor\Actor.
 */

namespace CultuurNet\UDB3\Actor;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Label;

abstract class Actor extends EventSourcedAggregateRoot
{
    /**
     * The labels.
     *
     * @var array
     */
    protected $labels = array();

    /**
     * Apply actor imported from UDB2.
     *
     * @param ActorImportedFromUDB2 $actorImported
     *   The imported actor.
     */
    protected function applyActorImportedFromUDB2(
        ActorImportedFromUDB2 $actorImported
    ) {
        $this->actorId = $actorImported->getActorId();

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImported->getCdbXmlNamespaceUri(),
            $actorImported->getCdbXml()
        );

        $this->labels = array();
        foreach (array_values($udb2Actor->getKeywords()) as $udb2Keyword) {
            $keyword = trim($udb2Keyword);
            if ($keyword) {
                $this->labels[] = new Label($keyword);
            }
        }
    }
}
