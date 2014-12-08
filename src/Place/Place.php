<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\Place.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Cdb\ActorItemFactory;

class Place extends EventSourcedAggregateRoot
{
    protected $placeId;

    /**
     * Factory method to create a new place.
     *
     * @param string $placeId
     * @return Place
     */
    public static function create($placeId)
    {
        $event = new self();
        $event->apply(new ActorCreated($placeId));

        return $event;
    }

    /**
     * @param string $eventId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     * @return Place
     */
    public static function importFromUDB2(
        $eventId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $event = new self();
        $event->apply(
            new ActorImportedFromUDB2(
                $eventId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->placeId;
    }

    protected function applyActorImportedFromUDB2(
        ActorImportedFromUDB2 $actorImported
    ) {
        $this->placeId = $actorImported->getActorId();

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImported->getCdbXmlNamespaceUri(),
            $actorImported->getCdbXml()
        );

        $this->keywords = array();
        foreach (array_values($udb2Actor->getKeywords()) as $udb2Keyword) {
            $this->keywords[] = new Keyword($udb2Keyword);
        }
    }

}
