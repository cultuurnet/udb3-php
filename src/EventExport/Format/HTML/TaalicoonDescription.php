<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use ValueObjects\Enum\Enum;

class TaalicoonDescription extends Enum
{
    const EEN_TAALICOON = "Je spreekt nog geen of niet zo veel Nederlands.";
    const TWEE_TAALICONEN = "Je begrijpt al een beetje Nederlands, maar je durft nog niet zo goed praten.";
    const DRIE_TAALICONEN = "Je begrijpt al veel Nederlands en je kan ook al iets vertellen.";
    const VIER_TAALICONEN = "Je spreekt en begrijpt vlot Nederlands.";
}
