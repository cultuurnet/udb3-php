<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


use CultuurNet\Search\SearchResult;
use CultuurNet\UDB3\Cdb\EventLD;
use CultuurNet\UDB3\SearchAPI2\SearchServiceInterface;

class DefaultEventService implements EventServiceInterface
{
    /**
     * @var SearchServiceInterface
     */
    protected $searchAPI2;

    /**
     * Callback to call to get the local IRI of an event.
     *
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    public function __construct(SearchServiceInterface $searchAPI2, IriGeneratorInterface $iriGenerator)
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
                new \CultuurNet\Search\Parameter\Query($cdbidCondition),
                new \CultuurNet\Search\Parameter\Group(),
            ));

        $result = SearchResult::fromXml(new \SimpleXMLElement($response->getBody(true), 0, false, \CultureFeed_Cdb_Default::CDB_SCHEME_URL));

        // @todo Decent exception handling.
        if ($result->getCurrentCount() !== 1) {
            throw new \Exception('Event should occur exactly once. Occurrences: ' . $result->getCurrentCount());
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
