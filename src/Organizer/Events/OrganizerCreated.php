<?php

/**
 * @file
 * Contains \namespace CultuurNet\UDB3\Organizer\Events\OrganizerCreated.
 */

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Title;

/**
 * Instantiates an OrganizerCreated event
 */
final class OrganizerCreated extends OrganizerEvent
{

    /**
     * @var Title
     */
    public $title;

    /**
     * @var Address[]
     */
    public $addresses;

    /**
     * @var string[]
     */
    public $phones;

    /**
     * @var string[]
     */
    public $emails;

    /**
     * @var string[]
     */
    public $urls;

    /**
     * @param string $id
     * @param Title $title
     * @param Address[] $addresses
     * @param string[] $phones
     * @param string[] $emails
     * @param string[] $urls
     */
    public function __construct(string $id, Title $title, array $addresses, array $phones, array $emails, array $urls)
    {
        parent::__construct($id);

        $this->guardAddressTypes(...$addresses);

        $this->title = $title;
        $this->addresses = $addresses;
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
    }

    /**
     * @param Address[] $addresses
     */
    private function guardAddressTypes(Address ...$addresses): void
    {
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return Address[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    /**
     * @return string[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @return string[]
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {

        $addresses = array();
        foreach ($this->getAddresses() as $address) {
            $addresses[] = $address->serialize();
        }

        return parent::serialize() + array(
          'title' => (string) $this->getTitle(),
          'addresses' => $addresses,
          'phones' => $this->getPhones(),
          'emails' => $this->getEmails(),
          'urls' => $this->getUrls(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data): OrganizerCreated
    {

        $addresses = array();
        foreach ($data['addresses'] as $address) {
            $addresses[] = Address::deserialize($address);
        }

        return new self(
            $data['organizer_id'],
            new Title($data['title']),
            $addresses,
            $data['phones'],
            $data['emails'],
            $data['urls']
        );
    }
}
