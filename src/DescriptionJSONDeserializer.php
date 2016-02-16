<?php

namespace CultuurNet\UDB3;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class DescriptionJSONDeserializer extends JSONDeserializer
{
    /**
     * @param String $data
     * @return Description
     */
    public function deserialize(String $data)
    {
        $data = parent::deserialize($data);

        if (!isset($data->description)) {
            throw new MissingValueException('Missing value for "description".');
        }

        return new Description($data->description);
    }
}
