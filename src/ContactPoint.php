<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * ContactPoint info.
 */
class ContactPoint implements SerializableInterface, JsonLdSerializableInterface
{
    /**
     * @var array
     */
    protected $phones = array();

    /**
     * @var array
     */
    protected $emails = array();

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * Constructor.
     * @param array $phones
     * @param array $emails
     * @param array $urls
     */
    public function __construct(array $phones = array(), array $emails = array(), array $urls = array())
    {
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
    }

    /**
     * @return array
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'phone' => $this->phones,
          'email' => $this->emails,
          'url' => $this->urls,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['phone'], $data['email'], $data['url']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd()
    {
        // Serialized version is the same.
        return $this->serialize();
    }

    /**
     * @param ContactPoint $otherContactPoint
     * @return bool
     */
    public function sameAs(ContactPoint $otherContactPoint)
    {
        return $this->toJsonLd() == $otherContactPoint->toJsonLd();
    }
}
