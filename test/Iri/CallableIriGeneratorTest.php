<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Iri;

class CallableIriGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testUsesResultOfCallable()
    {
        $baseUrl = 'http://example.com/';
        $fn = function ($id) use ($baseUrl) {
            return $baseUrl . $id;
        };

        $iriGenerator = new CallableIriGenerator($fn);

        $this->assertEquals(
            'http://example.com/foo',
            $iriGenerator->iri('foo')
        );
        $this->assertEquals(
            'http://example.com/bar',
            $iriGenerator->iri('bar')
        );
    }
}
