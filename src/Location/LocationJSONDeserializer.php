<?php

namespace CultuurNet\UDB3\Location;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use ValueObjects\String\String as StringLiteral;

class LocationJSONDeserializer extends JSONDeserializer
{
    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);
    }

    /**
     * @param StringLiteral $data
     *
     * @return Location
     *
     * @throws DataValidationException
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        $errors = [];
        $requiredArguments = ['id', 'name', 'address'];

        foreach ($requiredArguments as $requiredArgument) {
            if (!isset($data[$requiredArgument])) {
                $errors[] = "{$requiredArgument} is required but could not be found.";
            } elseif (empty($data[$requiredArgument])) {
                $errors[] = "{$requiredArgument} should not be empty.";
            }
        }

        if (!empty($errors)) {
            $validationException = new DataValidationException();
            $validationException->setValidationMessages($errors);
            throw $validationException;
        }

        $data['cdbid'] = $data['id'];
        unset($data['id']);

        return Location::deserialize($data);
    }
}
