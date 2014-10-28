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
    /**
     * Generate a derefereneable IRI.
     *
     * @param string $item
     *  A string uniquely identifying the subject.
     *
     * @return string
     *   A dereferenceable IRI.
     */
    public function iri($item);
} 
