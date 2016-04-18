<?php

namespace CultuurNet\UDB3;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String as StringLiteral;

class TitleJSONDeserializer extends JSONDeserializer
{
    /**
     * @param StringLiteral $data
     * @return Title
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        if (!isset($data->title)) {
            throw new MissingValueException('Missing value for "title".');
        }

        return new Title($data->title);
    }
}
