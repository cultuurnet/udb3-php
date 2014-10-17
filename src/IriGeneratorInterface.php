<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

/**
 * Generates dereferenceable IRIs for JSON-LD subjects.
 *
 * @see http://en.wikipedia.org/wiki/Dereferenceable_Uniform_Resource_Identifier
 */
interface IriGeneratorInterface
{
    public function iri($item);
} 
