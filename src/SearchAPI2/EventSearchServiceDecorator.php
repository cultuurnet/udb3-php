<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Search\Parameter;

class EventSearchServiceDecorator extends SearchServiceDecorator
{
    /**
     * {@inheritdoc}
     */
    public function search(array $params)
    {
        $params[] = new Parameter\FilterQuery('type:event');

        // include past events and present events with an embargo date
        $params[] = new Parameter\BooleanParameter('past', true);
        $params[] = new Parameter\BooleanParameter('unavailable', true);

        return $this->decoratedSearchService->search($params);
    }
}
