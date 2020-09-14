<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Facility.
 */

namespace CultuurNet\UDB3;

/**
 * Instantiates a Facility category.
 */
class Facility extends Category
{
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Category\Category as Udb3ModelCategory;
use InvalidArgumentException;

final class Facility extends Category
{
    const DOMAIN = 'facility';

    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }

    public static function deserialize(array $data): Facility
    {
        return new self($data['id'], $data['label']);
    }

    public static function fromUdb3ModelCategory(Udb3ModelCategory $category): Facility
    {
        $label = $category->getLabel();

        if (is_null($label)) {
            throw new InvalidArgumentException('Category label is required.');
        }

        return new self(
            $category->getId()->toString(),
            $label->toString()
        );
    }
}
