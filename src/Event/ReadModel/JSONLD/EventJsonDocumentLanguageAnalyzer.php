<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\ReadModel\ConfigurableJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class EventJsonDocumentLanguageAnalyzer extends ConfigurableJsonDocumentLanguageAnalyzer
{
    public function __construct()
    {
        parent::__construct(
            [
                'name',
                'description',
            ]
        );
    }
}
