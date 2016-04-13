<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;

interface EntityIriGeneratorFactoryInterface
{
    /**
     * @param EntityType $entityType
     *
     * @return IriGeneratorInterface
     */
    public function forEntityType(EntityType $entityType);
}
