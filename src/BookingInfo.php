<?php

/**
 * @file
 * Contains CultuurNet\UDB3\BookingInfo.
 */

namespace CultuurNet\UDB3;

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
     * Constructor.
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
          'availabilityEnds' => $this->availabilityEnds
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
    public function toJsonLd() {
        return $this->serialize();
    }

}
