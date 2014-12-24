<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\ActorRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Query;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
abstract class ActorRepository extends EntityRepository
{
    /**
     * Returns the default params.
     *
     * @param $id
     *
     * @return array
     */
    protected function getParams($id)
    {
        return array(
            new Query('cdbid:' . $id),
            new FilterQuery('type:actor')
        );
    }

  /**
     * {@inheritdoc}
     *
     * Ensures an actor is created, by importing it from UDB2 if it does not
     * exist locally yet.
     */
    public function load($id)
    {
        try {
            $actor = $this->decoratee->load($id);
        } catch (AggregateNotFoundException $e) {
            $params = $this->getParams($id);

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
                            $actorXml = $dom->saveXML();
                        }
                }
            }

            if (!isset($actorXml)) {
                throw AggregateNotFoundException::create($id);
            }

            $actor = $this->importFromUDB2(
                $id,
                $actorXml,
                \CultureFeed_Cdb_Default::CDB_SCHEME_URL
            );

            $this->add($actor);
        }

        return $actor;
    }
}
