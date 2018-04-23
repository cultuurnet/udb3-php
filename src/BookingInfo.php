<?php

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Model\ValueObject\Contact\BookingInfo as Udb3ModelBookingInfo;

/**
 * BookingInfo info.
 */
class BookingInfo implements JsonLdSerializableInterface
{
    /**
     * @var string|null
     */
    protected $phone;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $urlLabel;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $availabilityStarts;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $availabilityEnds;

    /**
     * @param string|null $url
     * @param string|null $urlLabel
     * @param string|null $phone
     * @param string|null $email
     * @param \DateTimeImmutable|null $availabilityStarts
     * @param \DateTimeImmutable|null $availabilityEnds
     */
    public function __construct(
        $url = null,
        $urlLabel = null,
        $phone = null,
        $email = null,
        \DateTimeImmutable $availabilityStarts = null,
        \DateTimeImmutable $availabilityEnds = null
    ) {
        // Workaround to maintain compatibility with older BookingInfo data.
        // Empty BookingInfo properties used to be stored as empty strings in the past.
        // Convert those to null in case they are injected via the constructor (via BookingInfo::deserialize()).
        // API clients are also allowed to send empty strings for BookingInfo properties via EntryAPI3, which should
        // also be treated as null.
        $url = $this->castEmptyStringToNull($url);
        $urlLabel = $this->castEmptyStringToNull($urlLabel);
        $phone = $this->castEmptyStringToNull($phone);
        $email = $this->castEmptyStringToNull($email);

        $this->url = $url;
        $this->urlLabel = $urlLabel;
        $this->phone = $phone;
        $this->email = $email;
        $this->availabilityStarts = $availabilityStarts;
        $this->availabilityEnds = $availabilityEnds;
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

    /**
     * @return \DateTimeImmutable|null
     */
    public function getAvailabilityStarts()
    {
        return $this->availabilityStarts;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getAvailabilityEnds()
    {
        return $this->availabilityEnds;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $serialized = array_filter(
            [
              'phone' => $this->phone,
              'email' => $this->email,
              'url' => $this->url,
              'urlLabel' => $this->urlLabel,
            ]
        );

        if ($this->availabilityStarts) {
            $serialized['availabilityStarts'] = $this->availabilityStarts->format(\DATE_ATOM);
        }

        if ($this->availabilityEnds) {
            $serialized['availabilityEnds'] = $this->availabilityEnds->format(\DATE_ATOM);
        }

        return $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $defaults = [
            'url' => null,
            'urlLabel' => null,
            'phone' => null,
            'email' => null,
            'availabilityStarts' => null,
            'availabilityEnds' => null,
        ];

        $data = array_merge($defaults, $data);

        $availabilityStarts = null;
        if ($data['availabilityStarts']) {
            $availabilityStarts = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $data['availabilityStarts']);
        }

        $availabilityEnds = null;
        if ($data['availabilityEnds']) {
            $availabilityEnds = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $data['availabilityEnds']);
        }

        return new static(
            $data['url'],
            $data['urlLabel'],
            $data['phone'],
            $data['email'],
            $availabilityStarts,
            $availabilityEnds
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
        $url = null;
        $urlLabel = null;
        $phone = null;
        $email = null;
        $availabilityStarts = null;
        $availabilityEnds = null;

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
            $availabilityStarts = $udb3ModelAvailability->getFrom();
            $availabilityEnds = $udb3ModelAvailability->getTo();
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

    /**
     * @param $string
     * @return null|string
     */
    private function castEmptyStringToNull($string)
    {
        return is_string($string) && strlen($string) === 0 ? null : $string;
    }
}
