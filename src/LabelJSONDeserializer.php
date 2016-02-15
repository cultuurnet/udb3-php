<?php

namespace CultuurNet\UDB3;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\String\String;

class LabelJSONDeserializer extends JSONDeserializer
{
    /**
     * {@inheritdoc}
     */
    public function deserialize(String $data)
    {
        $data = parent::deserialize($data);

        if (empty($data->label)) {
            throw new MissingValueException('Missing value "label"!');
        }

        return new Label($data->label);
    }
}
