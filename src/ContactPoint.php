<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ContactPoint.
 */

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
     * @var string
     */
    protected $type = '';

    /**
     * Constructor.
     */
    public function __construct(array $phones = array(), array $emails = array(), array $urls = array(), $type = '')
    {
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
        $this->type = $type;
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
}
