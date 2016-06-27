<?php

namespace CultuurNet\UDB3\Role\ValueObjects;

use ValueObjects\Enum\Enum;

class Permission extends Enum
{
    const AANBOD_INVOEREN = 'Aanbod invoeren';
    const AANBOD_BEWERKEN = 'Aanbod bewerken';
    const AANBOD_MODEREREN = 'Aanbod modereren';
    const AANBOD_VERWIJDEREN = 'Aanbod verwijderen';
    const ORGANISATIES_BEHEREN = 'Organisaties beheren';
    const GEBRUIKERS_BEHEREN = 'Gebruikers beheren';
    const LABELS_BEHEREN = 'Labels beheren';
}
