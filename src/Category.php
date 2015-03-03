<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Category.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Instantiates an UDB3 category.
 */
class Category implements SerializableInterface
{

    /**
     * The category ID.
     * @var string
     */
    protected $id;

    /**
     * The category label.
     * @var string
     */
    protected $label;

    /**
     * The domain.
     * @var string
     */
    protected $domain;

    public function __construct($id, $label, $domain)
    {

        if (empty($id)) {
            throw new \InvalidArgumentException('Category ID can not be empty.');
        }

        if (!is_string($domain)) {
            throw new \InvalidArgumentException('Domain should be a string.');
        }

        $this->id = $id;
        $this->label = $label;
        $this->domain = $domain;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
          'id' => $this->id,
          'label' => $this->label,
          'domain' => $this->domain,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
                $data['id'], $data['label'], $data['domain']
        );
    }
}
