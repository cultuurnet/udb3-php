<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

interface OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AbstractAddLabel
     */
    public function createAddLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     * @return AbstractDeleteLabel
     */
    public function createDeleteLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Language $language
     * @param String $title
     * @return mixed
     */
    public function createTranslateTitleCommand($id, Language $language, String $title);

    /**
     * @param $id
     * @param Language $language
     * @param String $description
     * @return mixed
     */
    public function createTranslateDescriptionCommand($id, Language $language, String $description);
}
