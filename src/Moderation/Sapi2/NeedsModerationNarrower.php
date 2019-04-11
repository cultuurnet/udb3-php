<?php

namespace CultuurNet\UDB3\Moderation\Sapi2;

use CultuurNet\UDB3\Search\Narrowing\EmbeddingQueryNarrower;

class NeedsModerationNarrower extends EmbeddingQueryNarrower
{
    public function __construct()
    {
        $parts = [
            '(%s)',
            'wfstatus:"readyforvalidation"',
            'startdate:[NOW TO *]',
        ];

        $query = implode(
            ' AND ',
            $parts
        );

        parent::__construct($query);
    }
}
