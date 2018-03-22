<?php

/**
 * @file
 * Contains CultuurNet\UDB3\BookingInfo.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Model\ValueObject\Contact\BookingInfo as Udb3ModelBookingInfo;

/**
 * BookingInfo info.
 */
class BookingInfo implements JsonLdSerializableInterface
{

    /**
     * @var string
     */
    protected $price = '';

    /**
     * @var string
     */
    protected $currency = 'EUR';

    /**
     * @var string
     */
    protected $phone = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $urlLabel = '';

    /**
     * @var string
     */
    protected $availabilityStarts = '';

    /**
     * @var string
     */
    protected $availabilityEnds = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @param string $url
     * @param string $urlLabel
     * @param string $phone
     * @param string $email
     * @param string $availabilityStarts
     * @param string $availabilityEnds
     * @param string $name
     * @param string $description
     */
    public function __construct(
        $url = '',
        $urlLabel = '',
        $phone = '',
        $email = '',
        $availabilityStarts = '',
        $availabilityEnds = '',
        $name = '',
        $description = ''
    ) {
        $this->url = $url;
        $this->urlLabel = $urlLabel;
        $this->phone = $phone;
        $this->email = $email;
        $this->availabilityStarts = $availabilityStarts;
        $this->availabilityEnds = $availabilityEnds;
        $this->name = $name;
        $this->description = $description;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getUrlLabel()
    {
        return $this->urlLabel;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAvailabilityStarts()
    {
        return $this->availabilityStarts;
    }

    public function getAvailabilityEnds()
    {
        return $this->availabilityEnds;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'phone' => $this->phone,
          'email' => $this->email,
          'url' => $this->url,
          'urlLabel' => $this->urlLabel,
          'name' => $this->name,
          'description' => $this->description,
          'availabilityStarts' => $this->availabilityStarts,
          'availabilityEnds' => $this->availabilityEnds,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['url'], $data['urlLabel'], $data['phone'], $data['email'],
            $data['availabilityStarts'], $data['availabilityEnds'], $data['name'], $data['description']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd()
    {
        return $this->serialize();
    }

    /**
     * @param BookingInfo $otherBookingInfo
     * @return bool
     */
    public function sameAs(BookingInfo $otherBookingInfo)
    {
        return $this->toJsonLd() === $otherBookingInfo->toJsonLd();
    }

    /**
     * @param Udb3ModelBookingInfo $udb3ModelBookingInfo
     * @return BookingInfo
     */
    public static function fromUdb3ModelBookingInfo(Udb3ModelBookingInfo $udb3ModelBookingInfo)
    {
        $url = '';
        $urlLabel = '';
        $phone = '';
        $email = '';
        $availabilityStarts = '';
        $availabilityEnds = '';

        if ($udb3ModelWebsite = $udb3ModelBookingInfo->getWebsite()) {
            $url = $udb3ModelWebsite->getUrl()->toString();
            $urlLabel = $udb3ModelWebsite->getLabel()->getTranslation(
                $udb3ModelWebsite->getLabel()->getOriginalLanguage()
            )->toString();
        }

        if ($udb3ModelPhone = $udb3ModelBookingInfo->getTelephoneNumber()) {
            $phone = $udb3ModelPhone->toString();
        }

        if ($udb3ModelEmail = $udb3ModelBookingInfo->getEmailAddress()) {
            $email = $udb3ModelEmail->toString();
        }

        if ($udb3ModelAvailability = $udb3ModelBookingInfo->getAvailability()) {
            $availabilityStarts = $udb3ModelAvailability->getFrom()->format(\DATE_ATOM);
            $availabilityEnds = $udb3ModelAvailability->getTo()->format(\DATE_ATOM);
        }

        return new BookingInfo(
            $url,
            $urlLabel,
            $phone,
            $email,
            $availabilityStarts,
            $availabilityEnds
        );
    }
}
