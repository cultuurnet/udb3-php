<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Actor\Actor.
 */

namespace CultuurNet\UDB3\Actor;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Label;

class Actor extends EventSourcedAggregateRoot
{
    /**
     * The actor id.
     *
     * @var string
     */
    protected $actorId;

    /**
     * The labels.
     *
     * @var array
     */
    protected $labels = array();

    /**
     * Import from UDB2.
     *
     * @param string $actorId
     *   The actor id.
     * @param string $cdbXml
     *   The cdb xml.
     * @param string $cdbXmlNamespaceUri
     *   The cdb xml namespace uri.
     *
     * @return Actor
     *   The actor.
     */
    public static function importFromUDB2(
        $actorId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $class = get_called_class();
        $actor = new $class;
        $actor->apply(
            new ActorImportedFromUDB2(
                $actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $actor;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->actorId;
    }

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
