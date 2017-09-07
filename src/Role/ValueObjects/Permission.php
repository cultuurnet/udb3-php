<?php

namespace CultuurNet\UDB3\Role\ValueObjects;

use ValueObjects\Enum\Enum;

/**
 * Class Permission
 * @package CultuurNet\UDB3\Role\ValueObjects
 * @method static Permission AANBOD_BEWERKEN()
 * @method static Permission AANBOD_MODEREREN()
 * @method static Permission AANBOD_VERWIJDEREN()
 * @method static Permission ORGANISATIES_BEHEREN()
 * @method static Permission GEBRUIKERS_BEHEREN()
 * @method static Permission LABELS_BEHEREN()
 * @method static Permission MEDIA_UPLOADEN()
 * @method static Permission AANBOD_LABELEN()
 */
class Permission extends Enum
{
    const AANBOD_BEWERKEN = 'Aanbod bewerken';
    const AANBOD_MODEREREN = 'Aanbod modereren';
    const AANBOD_VERWIJDEREN = 'Aanbod verwijderen';
    const ORGANISATIES_BEHEREN = 'Organisaties beheren';
    const GEBRUIKERS_BEHEREN = 'Gebruikers beheren';
    const LABELS_BEHEREN = 'Labels beheren';
    const MEDIA_UPLOADEN = 'Media uploaden';
    const AANBOD_LABELEN = 'Aanbod labelen';
}
