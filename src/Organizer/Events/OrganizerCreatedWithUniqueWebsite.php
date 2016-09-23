<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

/**
 * Instantiates an OrganizerCreatedWithUniqueWebsite event
 */
class OrganizerCreatedWithUniqueWebsite extends OrganizerEvent
{
    /**
     * @var Url
     */
    protected $website;

    /**
     * @var Title
     */
    public $title;

    /**
     * @var Address[]
     */
    public $addresses;

    /**
     * @var ContactPoint
     */
    private $contactPoint;

    /**
     * @param string $id
     * @param Url $website
     * @param Title $title
     * @param Address[] $addresses
     * @param ContactPoint $contactPoint
     */
    public function __construct(
        $id,
        Url $website,
        Title $title,
        array $addresses,
        ContactPoint $contactPoint
    ) {
        parent::__construct($id);

        $this->guardAddressTypes($addresses);

        $this->website = $website;
        $this->title = $title;
        $this->addresses = $addresses;
        $this->contactPoint = $contactPoint;
    }

    /**
     * @param Address[] $addresses
     */
    private function guardAddressTypes(array $addresses)
    {
        foreach ($addresses as $address) {
            if (!($address instanceof Address)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Argument should be of type Address, %s given.",
                        is_object($address) ? get_class($address) : 'scalar'
                    )
                );
            }
        }
    }

    /**
     * @return Url
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return ContactPoint
     */
    public function getContactPoint()
    {
        return $this->contactPoint;
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
            'website' => (string) $this->getWebsite(),
            'title' => (string) $this->getTitle(),
            'addresses' => $addresses,
            'contactPoint' => $this->contactPoint->serialize(),
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
            $data['organizer_id'],
            Url::fromNative($data['website']),
            new Title($data['title']),
            $addresses,
            ContactPoint::deserialize($data['contactPoint'])
        );
    }
}
