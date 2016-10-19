<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * ContactPoint info.
 * @todo Remove $type? Seems unused throughout the rest of the codebase.
 * @see https://jira.uitdatabank.be/browse/III-1508
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
     * @var string
     */
    protected $type = '';

    /**
     * Constructor.
     * @param array $phones
     * @param array $emails
     * @param array $urls
     * @param string $type
     */
    public function __construct(array $phones = array(), array $emails = array(), array $urls = array(), $type = '')
    {
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
        $this->type = $type;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
          'type' => $this->type,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['phone'], $data['email'], $data['url'], $data['type']
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
