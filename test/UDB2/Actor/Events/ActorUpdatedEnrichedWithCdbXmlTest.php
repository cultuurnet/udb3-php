<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor\Events;

use ValueObjects\String\String;

class ActorUpdatedEnrichedWithCdbXmlTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $id = new String('foo');
        $time = new \DateTimeImmutable();
        $author = new String('me@example.com');
        $cdbXml = new String(file_get_contents(__DIR__ . '/actor.xml'));
        $cdbXmlNamespaceUri = new String(
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $event = new ActorUpdatedEnrichedWithCdbXml(
            $id,
            $time,
            $author,
            $cdbXml,
            $cdbXmlNamespaceUri
        );

        $this->assertEquals($id, $event->getActorId());
        $this->assertEquals($time, $event->getTime());
        $this->assertEquals($author, $event->getAuthor());
        $this->assertEquals($cdbXml, $event->getCdbXml());
        $this->assertEquals($cdbXmlNamespaceUri, $event->getCdbXmlNamespaceUri());
    }
}
