<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class PlaceRepository implements RepositoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $decoratee;

    /**
     * @var SearchServiceInterface
     */
    protected $search;

    /**
     * @var EntryAPIImprovedFactory
     */
    protected $entryAPIImprovedFactory;

    /**
     * @var boolean
     */
    protected $syncBack = false;

    /**
     * @var EventStreamDecoratorInterface[]
     */
    private $eventStreamDecorators = array();

    public function __construct(
        RepositoryInterface $decoratee,
        SearchServiceInterface $search,
        EntryAPIImprovedFactory $entryAPIImprovedFactory,
        array $eventStreamDecorators = array()
    ) {
        $this->decoratee = $decoratee;
        $this->search = $search;
        $this->entryAPIImprovedFactory = $entryAPIImprovedFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    public function syncBackOn()
    {
        $this->syncBack = true;
    }

    public function syncBackOff()
    {
        $this->syncBack = false;
    }

    private function getType()
    {
        return '\\CultuurNet\\UDB3\\Place\\Place';
    }

    /**
     * {@inheritdoc}
     */
    public function add(AggregateRoot $aggregate)
    {
        if ($this->syncBack) {
            // We can not directly act on the aggregate, as the uncommitted events will
            // be reset once we retrieve them, therefore we clone the object.
            $double = clone $aggregate;
            $domainEventStream = $double->getUncommittedEvents();
            $eventStream = $this->decorateForWrite(
                $aggregate,
                $domainEventStream
            );

        }

        $this->decoratee->add($aggregate);
    }

    /**
     * @param Metadata $metadata
     * @return EntryAPI
     */
    private function createImprovedEntryAPIFromMetadata(Metadata $metadata)
    {
        $metadata = $metadata->serialize();
        if (!isset($metadata['uitid_token_credentials'])) {
            throw new \RuntimeException('No token credentials found. They are needed to access the entry API, so aborting request.');
        }
        $tokenCredentials = $metadata['uitid_token_credentials'];
        $entryAPI = $this->entryAPIImprovedFactory->withTokenCredentials(
            $tokenCredentials
        );

        return $entryAPI;
    }

    private function decorateForWrite(
        AggregateRoot $aggregate,
        DomainEventStream $eventStream
    ) {
        $aggregateType = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite(
                $aggregateType,
                $aggregateIdentifier,
                $eventStream
            );
        }

        return $eventStream;
    }

    /**
     * {@inheritdoc}
     *
     * Ensures an event is created, by importing it from UDB2 if it does not
     * exist locally yet.
     */
    public function load($id)
    {
        try {
            $event = $this->decoratee->load($id);
        } catch (AggregateNotFoundException $e) {

            $params = array(
              new Query('cdbid:' . $id),
              new Query('type:actor')
            );
            $results = $this->search->search($params);

            $cdbXml = $results->getBody(true);

            $reader = new \XMLReader();

            $reader->xml($cdbXml);

            while ($reader->read()) {
                switch ($reader->nodeType) {
                    case ($reader::ELEMENT):
                        if ($reader->localName == "actor" &&
                            $reader->getAttribute('cdbid') == $id
                        ) {
                            $node = $reader->expand();
                            $dom = new \DomDocument('1.0');
                            $n = $dom->importNode($node, true);
                            $dom->appendChild($n);
                            $eventXml = $dom->saveXML();
                        }
                }
            }

            if (!isset($eventXml)) {
                throw AggregateNotFoundException::create($id);
            }

            $event = Place::importFromUDB2(
                $id,
                $eventXml,
                \CultureFeed_Cdb_Default::CDB_SCHEME_URL
            );
        }

        return $event;
    }
}
