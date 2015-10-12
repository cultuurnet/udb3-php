<?php

/**
 * @file
 * Contains \namespace CultuurNet\UDB3\Organizer\Events\OrganizerCreated.
 */

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Title;

/**
 * Instantiates an OrganizerCreated event
 */
class OrganizerCreated extends OrganizerEvent
{

    /**
     * @var Title
     */
    public $title;

    /**
     * @var array
     */
    public $addresses;

    /**
     * @var array
     */
    public $phones;

    /**
     * @var array
     */
    public $emails;

    /**
     * @var array
     */
    public $urls;

    public function __construct($id, Title $title, array $addresses, array $phones, array $emails, array $urls)
    {
        parent::__construct($id);
        $this->title = $title;
        $this->addresses = $addresses;
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function getPhones()
    {
        return $this->phones;
    }

    public function getEmails()
    {
        return $this->emails;
    }

    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @return array
     */
    public function serialize()
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
    public static function deserialize(array $data)
    {

        $addresses = array();
        foreach ($data['addresses'] as $address) {
            $addresses[] = Address::deserialize($address);
        }

        return new static(
            $data['organizer_id'], new Title($data['title']), $addresses, $data['phones'], $data['emails'], $data['urls']
        );
    }
}
