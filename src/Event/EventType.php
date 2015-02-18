<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Category;


class EventType extends Category
{
    const DOMAIN = 'eventtype';

    public function __construct($id, $label)
    {
        parent::__construct($id, $label, self::DOMAIN);
    }

}
