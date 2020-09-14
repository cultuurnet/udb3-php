<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Category;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Category\Category as Udb3ModelCategory;
use InvalidArgumentException;

final class EventType extends Category
{
    const DOMAIN = 'eventtype';


    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }

    public static function deserialize(array $data): EventType
    {
        return new self($data['id'], $data['label']);
    }

    public static function fromJSONLDEvent(string $eventString): ?EventType
    {
        $event = json_decode($eventString, false);
        foreach ($event->terms as $term) {
            if ($term->domain === self::DOMAIN) {
                return new self($term->id, $term->label);
            }
        }
        return null;
    }

    public static function fromUdb3ModelCategory(Udb3ModelCategory $category): EventType
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
