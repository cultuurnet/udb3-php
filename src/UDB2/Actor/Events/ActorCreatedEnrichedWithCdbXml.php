<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor\Events;

use CultuurNet\UDB2DomainEvents\ActorCreated;
use CultuurNet\UDB3\Cdb\CdbXmlContainerInterface;
use CultuurNet\UDB3\HasCdbXmlTrait;
use ValueObjects\String\String;

class ActorCreatedEnrichedWithCdbXml extends ActorCreated implements CdbXmlContainerInterface
{
    use HasCdbXmlTrait;

    public function __construct(
        String $actorId,
        \DateTimeImmutable $time,
        String $author,
        String $cdbXml,
        String $cdbXmlNamespaceUri
    ) {
        parent::__construct(
            $actorId,
            $time,
            $author
        );

        $this->setCdbXml($cdbXml);
        $this->setCdbXmlNamespaceUri($cdbXmlNamespaceUri);
    }

    public static function fromActorCreated(
        ActorCreated $actorCreated,
        String $cdbXml,
        String $cdbXmlNamespaceUri
    ) {
        return new self(
            $actorCreated->getActorId(),
            $actorCreated->getTime(),
            $actorCreated->getAuthor(),
            $cdbXml,
            $cdbXmlNamespaceUri
        );
    }
}
