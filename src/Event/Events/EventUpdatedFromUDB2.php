<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\HasCdbXmlTrait;

class EventUpdatedFromUDB2 extends EventEvent implements EventCdbXMLInterface
{
    use HasCdbXmlTrait;

    public function __construct(string $eventId, string $cdbXml, string $cdbXmlNamespaceUri)
    {
        parent::__construct($eventId);

        $this->setCdbXml($cdbXml);
        $this->setCdbXmlNamespaceUri($cdbXmlNamespaceUri);
    }

    public function serialize(): array
    {
        return parent::serialize() + array(
            'cdbxml' => $this->getCdbXml(),
            'cdbXmlNamespaceUri' => $this->getCdbXmlNamespaceUri(),
        );
    }

    public static function deserialize(array $data): EventUpdatedFromUDB2
    {
        $data += array(
            'cdbXmlNamespaceUri' => \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2'),
        );
        return new self(
            $data['event_id'],
            $data['cdbxml'],
            $data['cdbXmlNamespaceUri']
        );
    }
}
