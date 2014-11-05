<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


use Broadway\EventStore\EventStreamNotFoundException;
use CultuurNet\Search\Parameter\Group;
use CultuurNet\Search\Parameter\Query;
use CultuurNet\Search\SearchResult;
use CultuurNet\UDB3\Cdb\EventLD;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\SearchAPI2 as SearchAPI2;

/**
 * Default EventServiceInterface implementation that uses Search API 2.
 */
class DefaultEventService implements EventServiceInterface
{
    /**
     * @var SearchAPI2\SearchServiceInterface
     */
    protected $searchAPI2;

    /**
     * Callback to call to get the local IRI of an event.
     *
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * Constructs a new DefaultEventService.
     *
     * @param SearchAPI2\SearchServiceInterface $searchAPI2
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(SearchAPI2\SearchServiceInterface $searchAPI2, IriGeneratorInterface $iriGenerator)
    {
        $this->searchAPI2 = $searchAPI2;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent($id)
    {
        $cdbidCondition = 'cdbid:' . $id;
        $response = $this->searchAPI2->search(array(
                new Query($cdbidCondition),
                new Group(),
            ));

        $result = SearchResult::fromXml(new \SimpleXMLElement($response->getBody(true), 0, false, \CultureFeed_Cdb_Default::CDB_SCHEME_URL));

        // @todo Decent exception handling.
        if ($result->getCurrentCount() !== 1) {
            throw new EventNotFoundException(sprintf('Event with id: %s not found.', $id));
        }

        foreach ($result->getItems() as $item) {
            /** @var \CultureFeed_Cdb_Item_Event $event */
            $cdbEvent = $item->getEntity();
            $iri = $this->iriGenerator->iri($cdbEvent->getCdbId());
            $event = new EventLD($iri, $cdbEvent);
            return $event;
        }
    }


} 
