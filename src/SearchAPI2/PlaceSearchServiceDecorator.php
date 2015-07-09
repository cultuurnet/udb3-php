<?php

namespace CultuurNet\UDB3\SearchAPI2;

use CultuurNet\Search\Parameter;

class PlaceSearchServiceDecorator extends SearchServiceDecorator
{
    /**
     * {@inheritdoc}
     */
    public function search(array $params)
    {
        $params[] = new Parameter\FilterQuery('type:actor');
        $params[] = new Parameter\FilterQuery('category_id:8.15.0.0.0');

        return $this->decoratedSearchService->search($params);
    }
}
