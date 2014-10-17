<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use CultuurNet\Search\Parameter;
use CultuurNet\Search\SearchResult;
use CultuurNet\UDB3\SearchAPI2;

/**
 * Search service implementation using Search API v2.
 */
class DefaultSearchService implements SearchServiceInterface
{
    /**
     * @var SearchAPI2\SearchServiceInterface
     */
    protected $searchAPI2;

    public function __construct(SearchAPI2\SearchServiceInterface $search)
    {
        $this->searchAPI2 = $search;
    }

    public function search($query, $limit = 30, $start = 0)
    {
        $qParam = new Parameter\Query($query);
        $groupParam = new Parameter\Group();
        $startParam = new Parameter\Start($start);
        $limitParam = new Parameter\Rows($limit);

        $params = array(
            $qParam,
            $groupParam,
            $limitParam,
            $startParam
        );

        $response = $this->searchAPI2->search($params);

        $result = SearchResult::fromXml(new \SimpleXMLElement($response->getBody(true), 0, false, \CultureFeed_Cdb_Default::CDB_SCHEME_URL));
        
        // @todo split this off to another class
        // @todo context and type should probably be injected at a higher level.
        $return = array(
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => $limit,
            'totalItems' => $result->getTotalCount(),
            'member' => array(),
        );

        foreach ($result->getItems() as $item) {
            /** @var \CultureFeed_Cdb_Item_Event $event */
            $event = $item->getEntity();
            // @todo Handle language dynamically, currently hardcoded to nl.
            /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
            $detail = $event->getDetails()->getDetailByLanguage('nl');
            $pictures = $detail->getMedia()->byMediaType(\CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO);
            $pictures->rewind();
            $picture = count($pictures) > 0 ? $pictures->current() : NULL;
            $return['member'][] = array(
                // @todo provide Event-LD context here
                // @todo make id a dereferenceable URI (http://en.wikipedia.org/wiki/Dereferenceable_Uniform_Resource_Identifier)
                '@context' => '/api/1.0/event.jsonld',
                '@id' => $item->getId(),
                'name' => $detail->getTitle(),
                'shortDescription' => $detail->getShortDescription(),
                'calendarSummary' => $detail->getCalendarSummary(),
                'image' => $picture ? $picture->getHLink() : NULL,
                'location' => $event->getLocation()->getLabel(),
            );
        }

        return $return;
    }
}
