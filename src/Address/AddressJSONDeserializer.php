<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use ValueObjects\String\String as StringLiteral;

class AddressJSONDeserializer extends JSONDeserializer
{
    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);
    }

    /**
     * @param StringLiteral $data
     * @return Address
     * @throws DataValidationException
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        $errors = [];
        $requiredArguments = ['streetAddress', 'postalCode', 'addressLocality', 'addressCountry'];

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

        return Address::deserialize($data);
    }
}
