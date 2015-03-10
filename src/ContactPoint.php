<?php

/**
 * @file
 * Contains CultuurNet\UDB3\ContactPoint.
 */

namespace CultuurNet\UDB3;

/**
 * ContactPoint info.
 */
class ContactPoint
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
     */
    public function __construct(array $phones = array(), array $emails = array(), array $urls = array())
    {
        $this->phones = $phones;
        $this->emails = $emails;
        $this->urls = $urls;
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'phones' => $this->phones,
          'emails' => $this->emails,
          'urls' => $this->urls,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['phones'], $data['emails'], $data['urls']
        );
    }
}
