<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Category.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Category\Category as Udb3ModelCategory;

/**
 * Instantiates an UDB3 category.
 */
class Category implements SerializableInterface, JsonLdSerializableInterface
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

    public function __construct(string $id, string $label, string $domain)
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
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
        return new self($data['id'], $data['label'], $data['domain']);
    }

    /**
     * {@inheritdoc}
     */
    public function toJsonLd(): array
    {
        // Matches the serialized array.
        return $this->serialize();
    }

    /**
     * @param Udb3ModelCategory $category
     * @return Category
     */
    public static function fromUdb3ModelCategory(Udb3ModelCategory $category)
    {
        if (is_null($category->getLabel())) {
            throw new \InvalidArgumentException('Category label is required.');
        }

        if (is_null($category->getDomain())) {
            throw new \InvalidArgumentException('Category domain is required.');
        }

        return new self(
            $category->getId()->toString(),
            $category->getLabel()->toString(),
            $category->getDomain()->toString()
        );
    }
}
