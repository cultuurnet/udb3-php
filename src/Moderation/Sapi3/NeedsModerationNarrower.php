<?php

namespace CultuurNet\UDB3\Moderation\Sapi3;

use CultuurNet\UDB3\Search\Narrowing\EmbeddingQueryNarrower;

class NeedsModerationNarrower extends EmbeddingQueryNarrower
{
    public function __construct()
    {
        $parts = [
            '(%s)',
            'workflowStatus:READY_FOR_VALIDATION',
            'availableRange:[now TO *]',
        ];

        $query = implode(
            ' AND ',
            $parts
        );

        parent::__construct($query);
    }
}
