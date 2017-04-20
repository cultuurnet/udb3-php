<?php

namespace CultuurNet\UDB3\Offer\ValueObjects;

use ValueObjects\Enum\Enum;

/**
 * Class EligibleCustomerType
 * @package CultuurNet\UDB3\Label\ValueObjects
 * @method static EligibleCustomerType EVERYONE()
 * @method static EligibleCustomerType MEMBERS()
 * @method static EligibleCustomerType EDUCATION()
 */
class EligibleCustomerType extends Enum
{
    const EVERYONE = 'everyone';
    const MEMBERS = 'members';
    const EDUCATION = 'education';
}
