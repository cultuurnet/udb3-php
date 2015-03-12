<?php

/**
 * @file
 * Contains CultuurNet\UDB3\JsonLdObjectInterface.
 */

namespace CultuurNet\UDB3;

/**
 * Interface for immutable objects that can be converted to json ld.
 */
interface JsonLdSerializable
{

    /**
     * Convert the object to json ld.
     */
    public function toJsonLd();

}
