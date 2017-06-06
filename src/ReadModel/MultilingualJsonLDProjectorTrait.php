<?php

namespace CultuurNet\UDB3\ReadModel;

use CultuurNet\UDB3\Language;

trait MultilingualJsonLDProjectorTrait
{
    /**
     * @param \stdClass $jsonLd
     * @param Language $language
     * @return \stdClass
     */
    protected function setMainLanguage(\stdClass $jsonLd, Language $language)
    {
        $jsonLd->mainLanguage = $language->getCode();
        return $jsonLd;
    }
}
