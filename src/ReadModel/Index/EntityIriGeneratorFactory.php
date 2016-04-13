<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\ReadModel\Index\EntityType;

class EntityIriGeneratorFactory implements EntityIriGeneratorFactoryInterface
{
    protected $baseUrl;

    /**
     * EntityIriGeneratorFactory constructor.
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
    
    public function forEntityType(EntityType $entityType)
    {
        $baseUrl = $this->baseUrl;
        return new CallableIriGenerator(
            function ($cdbid) use ($baseUrl, $entityType) {
                return $baseUrl . '/' . $entityType . '/' . $cdbid;
            }
        );
    }
}
